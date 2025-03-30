<?php

namespace App\Http\Controllers\Task;

use App\Enums\FlashMessageType;
use App\Enums\FlashMessageVariant;
use App\Helpers\FlashMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Mail\TaskAssigned;
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
    public function index()
    {
        // check if user can access this page, i.e. only admins and supervisors
        Gate::authorize('viewAll', Task::class);

        return Inertia::render('task/ShowAllTasks', [
            'tasks' => Task::all(),
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
        // fetch task with assignees and viewers
        $task = Task::with([
            'assignees:id',
            'viewers:id',
            'parent',
        ])->findOrFail($id);

        // check if user can view the task
        Gate::authorize('view', arguments: $task);

        $comments = $task->comments()->latest()->with('user', 'commentable')->get();

        // show the task
        return Inertia::render('task/ShowTask', [
            'can' => [
                'edit' => auth()->user()->can('edit', $task),
                'updateStatus' => auth()->user()->can('updateStatus', $task),
                'updateStatusToDone' => auth()->user()->can('updateStatusToDone', Task::class),
                'deleteComment' => auth()->user()->can('delete', Comment::class),
            ],
            'task' => $task,
            'statuses' => Status::all(),
            'priorities' => Priority::all(),
            'users' => User::all(),
            'supervisorsAndAdmins' => User::getAllSupervisorsAndAdmins()->get(),
            'comments' => $comments,
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

        // use db transaction
        $updatedTask = DB::transaction(function () use ($validated, $task, &$newAssignees, &$newViewers) {
            // Update the task with validated data
            $task->update([
                ...$validated,
                'updated_by' => auth()->id(),
            ]);

            // Update assignees if they exist in the request
            if (isset($validated['assignees'])) {
                $assigneeChanges = $task->assignees()->sync($validated['assignees']);
                $newAssignees = $assigneeChanges['attached'];
            }

            // Update viewers if they exist in the request
            if (isset($validated['viewers'])) {
                $viewerChanges = $task->viewers()->sync($validated['viewers']);
                $newViewers = $viewerChanges['attached'];
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
        // check if user can update status of task
        Gate::authorize('updateStatus', $task);

        // Validate the incoming request data
        $validated = $request->validate([
            'status_id' => ['required', 'exists:statuses,id'],
        ]);

        // get the name of done status
        $doneStatusId = Status::where('name', 'Done')->first()->id;

        // Check if user is trying to set status to "done"
        if ($validated['status_id'] == $doneStatusId) {
            // check if user can update status to done
            Gate::authorize('updateStatusToDone', $task);
        }

        // Update the task with the new status
        $task->update([
            'status_id' => $validated['status_id'],
        ]);

        // redirect to show page with flash message
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
        $validated = $request->validate([
            'content' => ['required', 'min:3'],
        ]);

        // create a comment
        $task->comments()->create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
        ]);

        // redirect to show page
        return to_route('tasks.show', $task->id);
    }
}
