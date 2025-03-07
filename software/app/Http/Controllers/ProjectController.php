<?php

namespace App\Http\Controllers;

use App\Enums\FlashMessageType;
use App\Enums\FlashMessageVariant;
use App\Helpers\FlashMessage;
use App\Http\Requests\StoreProjectRequest;
use App\Models\Comment;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use DB;
use Gate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    /**
     * Display all projects
     */
    public function index()
    {
        // check if user can access this page, i.e. only admins and supervisors
        Gate::authorize('viewAll', Project::class);

        return Inertia::render('Project/AllProjects', [
            'projects' => Project::all(),
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
        return Inertia::render('Project/CreateProject', [
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

        // use db transaction to create an app
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
            $project->slug = 'PROJECT-'.$project->id;
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
            'status:id',
            'priority:id',
            'supervisor:id',
            'assignees:id',
            'viewers:id',
        ])->findOrFail($id);

        // check if user can view the project
        Gate::authorize('view', arguments: $project);

        $comments = $project->comments()->latest()->with('user', 'commentable')->get();

        // show the project
        return Inertia::render('Project/ShowProject', [
            'can' => [
                'edit' => auth()->user()->can('edit', $project),
                'updateStatus' => auth()->user()->can('updateStatus', $project),
                'updateStatusToDone' => auth()->user()->can('updateStatusToDone', Project::class),
                'deleteComment' => auth()->user()->can('delete', Comment::class),
            ],
            'project' => $project,
            'statuses' => Status::all(),
            'priorities' => Priority::all(),
            'users' => User::all(),
            'supervisorsAndAdmins' => User::getAllSupervisorsAndAdmins()->get(),
            'comments' => $comments,
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

        // use db transaction
        $updatedProject = DB::transaction(function () use ($validated, $project) {
            // Update the project with validated data
            $project->update([
                ...$validated,
                'updated_by' => auth()->id(),
            ]);

            // Update assignees if they exist in the request
            if (isset($validated['assignees'])) {
                $project->assignees()->sync($validated['assignees']);
            }

            // Update viewers if they exist in the request
            if (isset($validated['viewers'])) {
                $project->viewers()->sync($validated['viewers']);
            }

            return $project;
        });

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

        // Check if user is trying to set status to "done"
        if ($validated['status_id'] == $doneStatusId) {
            // check if user can update status to done
            Gate::authorize('updateStatusToDone', $project);
        }

        // Update the project with the new status
        $project->update([
            'status_id' => $validated['status_id'],
        ]);

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
        $validated = $request->validate([
            'content' => ['required', 'min:3'],
        ]);

        // create a comment
        $project->comments()->create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
        ]);

        // redirect to show page
        return to_route('projects.show', $project);
    }
}
