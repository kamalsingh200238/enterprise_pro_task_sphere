<?php

namespace App\Http\Controllers\Project;

use App\Enums\FlashMessageType;
use App\Enums\FlashMessageVariant;
use App\Enums\UserRole;
use App\Helpers\FlashMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Mail\ProjectAssigned;
use App\Mail\ProjectInReview;
use App\Mail\ProjectViewerAssigned;
use App\Models\Comment;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use DB;
use Gate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Mail;

class ProjectController extends Controller
{
    /**
     * Display all projects
     */
    public function index(Request $request)
    {
        // check if user can access this page, i.e. only admins and supervisors
        Gate::authorize('viewAll', Project::class);

        $projects = Project::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return Inertia::render('project/ShowAllProjects', [
            'projects' => $projects,
            'search' => $request->input('search', ''),
        ]);
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        // check if user can access this page, i.e. only admins and supervisors
        Gate::authorize('create', Project::class);

        // show the create project form to user
        return Inertia::render('project/CreateProject', [
            'statuses' => Status::all(),
            'priorities' => Priority::all(),
            'users' => User::all(),
            'supervisorsAndAdmins' => User::getAllSupervisorsAndAdmins()->get(),
        ]);
    }

    /**
     * Create a new project in database
     */
    public function store(StoreProjectRequest $request)
    {
        // check if user can create a project
        Gate::authorize('create', Project::class);

        // get the validated data from request
        $validated = $request->validated();

        // use db transaction to create a project
        $project = DB::transaction(function () use ($validated) {
            // create a new project
            $project = Project::create(
                [
                    ...$validated,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]
            );

            // add slug in the project
            $project->slug = 'PRO-'.$project->id;
            $project->saveQuietly();

            // attach assignees if they exist
            if (isset($validated['assignees'])) {
                $project->assignees()->attach($validated['assignees']);
            }

            // attach viewers if they exist
            if (isset($validated['viewers'])) {
                $project->viewers()->attach($validated['viewers']);
            }

            // return project
            return $project;
        });

        // send emails to all assignees
        $project->load([
            'assignees' => function ($query) {
                $query->select('users.id', 'users.email', 'users.name');
            },
        ]);
        foreach ($project->assignees as $assignee) {
            if ($assignee->email) {
                Mail::to($assignee->email)->queue(new ProjectAssigned($project, $assignee));
            }
        }

        // if there are viewers then send the viewer email
        if (isset($validated['viewers']) && ! empty($validated['viewers'])) {
            $project->load([
                'viewers' => function ($query) {
                    $query->select('users.id', 'users.email', 'users.name');
                },
            ]);
            foreach ($project->viewers as $viewer) {
                if ($viewer->email) {
                    Mail::to($viewer->email)->queue(new ProjectViewerAssigned($project, $viewer));
                }
            }
        }

        // return to show all page with a success flash message
        return to_route('projects.show-all')
            ->with('flash', new FlashMessage(
                'Created Project Successfully',
                FlashMessageVariant::Success,
                FlashMessageType::CreatedProject,
                ['project' => $project]
            )->toArray());
    }

    /**
     * Display a project
     */
    public function show(int $id)
    {
        // fetch project with assignees and viewers
        $project = Project::with([
            'assignees:id',
            'viewers:id',
        ])->findOrFail($id);

        // check if user can view the project
        Gate::authorize('view', arguments: $project);

        $user = auth()->user();
        $isAdminOrSupervisor = $user->hasRole([UserRole::Admin, UserRole::Supervisor]);

        // Load the project with its tasks, subtasks, and all related comments using eager loading with constraints
        $project->load([
            // Load project comments
            'comments' => function ($query) {
                $query->with('user')->latest();
            },
            // Load tasks with permission filters
            'tasks' => function ($query) use ($user, $isAdminOrSupervisor) {
                $query->when(! $isAdminOrSupervisor, function ($q) use ($user) {
                    $q->where(function ($subQ) use ($user) {
                        $subQ->where('is_private', false)
                            ->orWhereHas('assignees', fn ($assigneeQ) => $assigneeQ->where('user_id', $user->id))
                            ->orWhereHas('viewers', fn ($viewerQ) => $viewerQ->where('user_id', $user->id));
                    });
                });
            },
            // Load task comments
            'tasks.comments' => function ($query) {
                $query->with('user')->latest();
            },
            // Load subtasks with permission filters
            'tasks.subtasks' => function ($query) use ($user, $isAdminOrSupervisor) {
                $query->when(! $isAdminOrSupervisor, function ($q) use ($user) {
                    $q->where(function ($subQ) use ($user) {
                        $subQ->where('is_private', false)
                            ->orWhereHas('assignees', fn ($assigneeQ) => $assigneeQ->where('user_id', $user->id))
                            ->orWhereHas('viewers', fn ($viewerQ) => $viewerQ->where('user_id', $user->id));
                    });
                });
            },
            // Load subtask comments
            'tasks.subtasks.comments' => function ($query) {
                $query->with('user')->latest();
            },
        ]);

        // show the project
        return Inertia::render('project/ShowProject', [
            'can' => [
                'edit' => auth()->user()->can('edit', $project),
                'updateStatus' => auth()->user()->can('updateStatus', $project),
                'updateStatusToDone' => auth()->user()->can('updateStatusToDone', Project::class),
                'deleteProject' => auth()->user()->can('delete', Project::class),
                'comment' => auth()->user()->can('createComment', $project),
                'deleteComment' => auth()->user()->can('delete', Comment::class),
            ],
            'project' => $project,
            'statuses' => Status::all(),
            'priorities' => Priority::all(),
            'users' => User::all(),
            'supervisorsAndAdmins' => User::getAllSupervisorsAndAdmins()->get(),
        ]);
    }

    /**
     *  Edit a project
     */
    public function edit(StoreProjectRequest $request, Project $project)
    {
        // check if user can edit project
        Gate::authorize('edit', $project);

        // Get validated data
        $validated = $request->validated();

        $newAssignees = [];
        $newViewers = [];
        $changingToReview = null;

        // use db transaction
        $updatedProject = DB::transaction(function () use ($validated, $project, &$changingToReview, &$newAssignees, &$newViewers) {
            $changingToReview = $validated['status_id'] !== $project->status_id && $validated['status_id'] === Status::where('name', 'In Review')->first()->id;

            // Update the project with validated data
            $project->update([
                ...$validated,
                'updated_by' => auth()->id(),
            ]);

            // Update assignees if they exist in the request
            if (isset($validated['assignees'])) {
                $assigneeChanges = $project->assignees()->sync($validated['assignees']);
                $newAssignees = $assigneeChanges['attached'];
                $project->touch();
            }

            // Update viewers if they exist in the request
            if (isset($validated['viewers'])) {
                $viewerChanges = $project->viewers()->sync($validated['viewers']);
                $newViewers = $viewerChanges['attached'];
                $project->touch();
            }

            return $project;
        });

        if (! empty($newAssignees)) {
            $users = User::whereIn('id', $newAssignees)->get();
            // send emails to all assignees
            foreach ($users as $user) {
                if ($user->email) {
                    Mail::to($user->email)->queue(new ProjectAssigned($project, $user));
                }
            }
        }

        if (! empty($newViewers)) {
            $users = User::whereIn('id', $newViewers)->get();
            // send emails to all assignees
            foreach ($users as $user) {
                if ($user->email) {
                    Mail::to($user->email)->queue(new ProjectAssigned($project, $user));
                }
            }
        }

        if ($changingToReview) {
            $user = User::find($project->supervisor_id);
            Mail::to($user)->queue(new ProjectInReview($project, $user));
        }

        // redirect to show page with flash message
        return to_route('projects.show', $updatedProject->id)
            ->with('flash', new FlashMessage(
                'Project Updated Successfully',
                FlashMessageVariant::Success,
                FlashMessageType::Normal,
            )->toArray());
    }

    /**
     * Update status of the project
     */
    public function updateStatus(Request $request, Project $project)
    {
        // check if user can update status of project
        Gate::authorize('updateStatus', $project);

        // Validate the incoming request data
        $validated = $request->validate([
            'status_id' => ['required', 'exists:statuses,id'],
        ]);

        // get the name of done status
        $doneStatusId = Status::where('name', 'Done')->first()->id;
        $inReviewStatusId = Status::where('name', 'In Review')->first()->id;

        // Check if user is trying to set status to "done"
        if ($validated['status_id'] === $doneStatusId) {
            // check if user can update status to done
            Gate::authorize('updateStatusToDone', $project);
        }

        // Update the project with the new status
        $project->update([
            'status_id' => $validated['status_id'],
        ]);

        if ($validated['status_id'] === $inReviewStatusId) {
            $user = User::find($project->supervisor_id);
            Mail::to($user)->queue(new ProjectInReview($project, $user));
        }

        // redirect to show page with flash message
        return to_route('projects.show', $project->id)
            ->with('flash', new FlashMessage(
                'Project Updated Successfully',
                FlashMessageVariant::Success,
                FlashMessageType::Normal,
            )->toArray());
    }

    /**
     * Delete a project
     */
    public function delete(Project $project)
    {
        // check if user can delete the project
        Gate::authorize('delete', $project);

        // delete the project
        $deleted = $project->delete();

        // if project deleted
        if ($deleted) {
            // redirect to show all page with success message
            return to_route('projects.show-all')
                ->with('flash', new FlashMessage(
                    'Project Deleted Succesfully',
                    FlashMessageVariant::Success,
                    FlashMessageType::Normal,
                )->toArray());
        }

        // if not deleted then send back with flash message
        return back()
            ->with('flash', new FlashMessage(
                'There was a problem in deleting project',
                FlashMessageVariant::Error,
                FlashMessageType::Normal,
            )->toArray());
    }

    /**
     * Add a comment in project
     */
    public function createComment(Request $request, Project $project)
    {
        // check if user can create comment in project
        Gate::authorize('createComment', $project);

        // validate the incoming request
        $validated = $request->validate(
            [
                'content' => ['required', 'min:3'],
            ],
            [
                'content.required' => 'Please provide a comment. It cannot be empty.',
                'content.min' => 'The comment must be at least 3 characters long.',
            ]
        );

        // create a comment
        $project->comments()->create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
        ]);

        $project->touch();

        // redirect to show page
        return to_route('projects.show', $project);
    }
}
