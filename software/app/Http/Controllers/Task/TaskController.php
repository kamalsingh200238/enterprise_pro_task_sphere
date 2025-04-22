<?php

namespace App\Http\Controllers\Task;

use App\Enums\FlashMessageType;
use App\Enums\FlashMessageVariant;
use App\Enums\UserRole;
use App\Helpers\FlashMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Mail\TaskAssigned;
use App\Mail\TaskInReview;
use App\Mail\TaskViewerAssigned;
use App\Models\Comment;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use DB;
use Gate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Mail;

class TaskController extends Controller
{
    /**
     * Display all tasks
     */
    public function index(Request $request)
    {
        // check if user can access this page, i.e. only admins and supervisors
        Gate::authorize('viewAll', Task::class);

        $tasks = Task::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return Inertia::render('task/ShowAllTasks', [
            'tasks' => $tasks,
            'search' => $request->input('search', ''),
        ]);
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        // check if user can access this page, i.e. only admins and supervisors
        Gate::authorize('create', Task::class);

        // show the create task form to user
        return Inertia::render('task/CreateTask', [
            'statuses' => Status::all(),
            'priorities' => Priority::all(),
            'users' => User::all(),
            'supervisorsAndAdmins' => User::getAllSupervisorsAndAdmins()->get(),
            'projects' => Project::all(),
        ]);
    }

    /**
     * Create a new task in database
     */
    public function store(StoreTaskRequest $request)
    {
        // check if user can create a task
        Gate::authorize('create', Task::class);

        // get the validated data from request
        $validated = $request->validated();

        // use db transaction to create a task
        $task = DB::transaction(function () use ($validated) {
            // create a new task
            $task = Task::create(
                [
                    ...$validated,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]
            );

            // add slug in the task
            $task->slug = 'TASK-'.$task->id;
            $task->saveQuietly();

            // attach assignees if they exist
            if (isset($validated['assignees'])) {
                $task->assignees()->attach($validated['assignees']);
            }

            // attach viewers if they exist
            if (isset($validated['viewers'])) {
                $task->viewers()->attach($validated['viewers']);
            }

            // return task
            return $task;
        });

        // send emails to all assignees
        $task->load([
            'assignees' => function ($query) {
                $query->select('users.id', 'users.email', 'users.name');
            },
        ]);
        foreach ($task->assignees as $assignee) {
            if ($assignee->email) {
                Mail::to($assignee->email)->queue(new TaskAssigned($task, $assignee));
            }
        }

        // if there are viewers then send the viewer email
        if (isset($validated['viewers']) && ! empty($validated['viewers'])) {
            $task->load([
                'viewers' => function ($query) {
                    $query->select('users.id', 'users.email', 'users.name');
                },
            ]);
            foreach ($task->viewers as $viewer) {
                if ($viewer->email) {
                    Mail::to($viewer->email)->queue(new TaskViewerAssigned($task, $viewer));
                }
            }
        }

        // return to show all page with a success flash message
        return to_route('tasks.show-all')
            ->with('flash', new FlashMessage(
                'Created Task Successfully',
                FlashMessageVariant::Success,
                FlashMessageType::CreatedTask,
                ['task' => $task]
            )->toArray());
    }

    /**
     * Display a task
     */
    public function show(int $id)
    {
        $user = auth()->user();
        $isAdminOrSupervisor = $user->hasRole([UserRole::Admin, UserRole::Supervisor]);

        // fetch task with assignees and viewers
        $task = Task::with([
            'assignees:id',
            'viewers:id',
            'parent',
            'comments' => function ($query) {
                $query->with('user')->latest();
            },
            // Load subtasks with permission filters
            'subtasks' => function ($query) use ($user, $isAdminOrSupervisor) {
                $query->when(! $isAdminOrSupervisor, function ($q) use ($user) {
                    $q->where(function ($subQ) use ($user) {
                        $subQ->where('is_private', false)
                            ->orWhereHas('assignees', fn ($assigneeQ) => $assigneeQ->where('user_id', $user->id))
                            ->orWhereHas('viewers', fn ($viewerQ) => $viewerQ->where('user_id', $user->id));
                    });
                });
            },
            // Load subtasks' comments
            'subtasks.comments' => function ($query) {
                $query->with('user')->latest();
            },
        ])->findOrFail($id);

        // check if user can view the task
        Gate::authorize('view', arguments: $task);

        // show the task
        return Inertia::render('task/ShowTask', [
            'can' => [
                'edit' => auth()->user()->can('edit', $task),
                'updateStatus' => auth()->user()->can('updateStatus', $task),
                'updateStatusToDone' => auth()->user()->can('updateStatusToDone', Task::class),
                'deleteTask' => auth()->user()->can('delete', Task::class),
                'comment' => auth()->user()->can('createComment', $task),
                'deleteComment' => auth()->user()->can('delete', Comment::class),
            ],
            'task' => $task,
            'statuses' => Status::all(),
            'priorities' => Priority::all(),
            'users' => User::all(),
            'supervisorsAndAdmins' => User::getAllSupervisorsAndAdmins()->get(),
            'projects' => Project::all(),
        ]);
    }

    /**
     *  Edit a task
     */
    public function edit(StoreTaskRequest $request, Task $task)
    {
        // check if user can edit task
        Gate::authorize('edit', $task);

        // Get validated data
        $validated = $request->validated();

        $newAssignees = [];
        $newViewers = [];
        $changingToReview = null;

        // use db transaction
        $updatedTask = DB::transaction(function () use ($validated, $task, &$changingToReview, &$newAssignees, &$newViewers) {
            $changingToReview = $validated['status_id'] !== $task->status_id && $validated['status_id'] === Status::where('name', 'In Review')->first()->id;
            // Update the task with validated data
            $task->update([
                ...$validated,
                'updated_by' => auth()->id(),
            ]);

            // Update assignees if they exist in the request
            if (isset($validated['assignees'])) {
                $assigneeChanges = $task->assignees()->sync($validated['assignees']);
                $newAssignees = $assigneeChanges['attached'];
                $task->touch();
            }

            // Update viewers if they exist in the request
            if (isset($validated['viewers'])) {
                $viewerChanges = $task->viewers()->sync($validated['viewers']);
                $newViewers = $viewerChanges['attached'];
                $task->touch();
            }

            return $task;
        });

        if (! empty($newAssignees)) {
            $users = User::whereIn('id', $newAssignees)->get();
            // send emails to all assignees
            foreach ($users as $user) {
                if ($user->email) {
                    Mail::to($user->email)->queue(new TaskAssigned($task, $user));
                }
            }
        }

        if (! empty($newViewers)) {
            $users = User::whereIn('id', $newViewers)->get();
            // send emails to all assignees
            foreach ($users as $user) {
                if ($user->email) {
                    Mail::to($user->email)->queue(new TaskViewerAssigned($task, $user));
                }
            }
        }

        if ($changingToReview) {
            $user = User::find($task->supervisor_id);
            Mail::to($user)->queue(new TaskInReview($task, $user));
        }

        // redirect to show page with flash message
        return to_route('tasks.show', $updatedTask->id)
            ->with('flash', new FlashMessage(
                'Task Updated Successfully',
                FlashMessageVariant::Success,
                FlashMessageType::Normal,
            )->toArray());
    }

    /**
     * Update status of the task
     */
    public function updateStatus(Request $request, Task $task)
    {
        Gate::authorize('updateStatus', $task);

        $validated = $request->validate([
            'status_id' => ['required', 'exists:statuses,id'],
        ]);

        $doneStatusId = Status::where('name', 'Done')->first()->id;
        $inReviewStatusId = Status::where('name', 'In Review')->first()->id;

        if ($validated['status_id'] === $doneStatusId) {
            Gate::authorize('updateStatusToDone', $task);
        }

        // Update the task status
        $task->update([
            'status_id' => $validated['status_id'],
        ]);

        if ($validated['status_id'] === $inReviewStatusId) {
            $user = User::find($task->supervisor_id);
            Mail::to($user)->queue(new TaskInReview($task, $user));
        }

        return to_route('tasks.show', $task->id)
            ->with('flash', new FlashMessage(
                'Task Updated Successfully',
                FlashMessageVariant::Success,
                FlashMessageType::Normal,
            )->toArray());
    }

    /**
     * Delete a task
     */
    public function delete(Task $task)
    {
        // check if user can delete the task
        Gate::authorize('delete', $task);

        // delete the task
        $deleted = $task->delete();

        // if task deleted
        if ($deleted) {
            // redirect to show all page with success message
            return to_route('tasks.show-all')
                ->with('flash', new FlashMessage(
                    'Task Deleted Succesfully',
                    FlashMessageVariant::Success,
                    FlashMessageType::Normal,
                )->toArray());
        }

        // if not deleted then send back with flash message
        return back()
            ->with('flash', new FlashMessage(
                'There was a problem in deleting task',
                FlashMessageVariant::Error,
                FlashMessageType::Normal,
            )->toArray());
    }

    /**
     * Add a comment in task
     */
    public function createComment(Request $request, Task $task)
    {
        // check if user can create comment in task
        Gate::authorize('createComment', $task);

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
        $task->comments()->create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
        ]);

        $task->touch();

        // redirect to show page
        return to_route('tasks.show', $task->id);
    }
}
