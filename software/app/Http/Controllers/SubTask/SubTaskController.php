<?php

namespace App\Http\Controllers\SubTask;

use App\Enums\FlashMessageType;
use App\Enums\FlashMessageVariant;
use App\Helpers\FlashMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubTask\StoreSubTaskRequest;
use App\Models\Comment;
use App\Models\Priority;
use App\Models\Status;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\User;
use DB;
use Gate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubTaskController extends Controller
{
    /**
     * Display all sub-tasks
     */
    public function index()
    {
        // check if user can access this page, i.e. only admins and supervisors
        Gate::authorize('viewAll', SubTask::class);

        return Inertia::render('sub-task/ShowAllSubTasks', [
            'subTasks' => SubTask::all(),
        ]);
    }

    /**
     * Show the form for creating a new sub-task.
     */
    public function create()
    {
        // check if user can access this page, i.e. only admins and supervisors
        Gate::authorize('create', SubTask::class);

        // show the create sub-task form to user
        return Inertia::render('sub-task/CreateSubTask', [
            'statuses' => Status::all(),
            'priorities' => Priority::all(),
            'users' => User::all(),
            'supervisorsAndAdmins' => User::getAllSupervisorsAndAdmins()->get(),
            'tasks' => Task::all(),
        ]);
    }

    /**
     * Create a new sub-task in database
     */
    public function store(StoreSubTaskRequest $request)
    {
        // check if user can create a sub-task
        Gate::authorize('create', SubTask::class);

        // get the validated data from request
        $validated = $request->validated();

        // use db transaction to create a sub-task
        $subTask = DB::transaction(function () use ($validated) {
            // create a new sub-task
            $subTask = SubTask::create(
                [
                    ...$validated,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]
            );

            // add slug in the sub-task
            $subTask->slug = 'SUB-'.$subTask->id;
            $subTask->saveQuietly();

            // attach assignees if they exist
            if (isset($validated['assignees'])) {
                $subTask->assignees()->attach($validated['assignees']);
            }

            // attach viewers if they exist
            if (isset($validated['viewers'])) {
                $subTask->viewers()->attach($validated['viewers']);
            }

            // return sub-task
            return $subTask;
        });

        // return to show all page with a success flash message
        return to_route('sub-tasks.show-all')
            ->with('flash', new FlashMessage(
                'Created Sub-task Successfully',
                FlashMessageVariant::Success,
                FlashMessageType::CreatedSubTask,
                ['subTask' => $subTask]
            )->toArray());
    }

    /**
     * Display a sub-task
     */
    public function show(int $id)
    {
        // fetch sub-task with assignees and viewers
        $subTask = SubTask::with([
            'assignees:id',
            'viewers:id',
            'parent',
        ])->findOrFail($id);

        // check if user can view the sub-task
        Gate::authorize('view', arguments: $subTask);

        $comments = $subTask->comments()->latest()->with('user', 'commentable')->get();

        // show the sub-task
        return Inertia::render('sub-task/ShowSubTask', [
            'can' => [
                'edit' => auth()->user()->can('edit', $subTask),
                'updateStatus' => auth()->user()->can('updateStatus', $subTask),
                'updateStatusToDone' => auth()->user()->can('updateStatusToDone', SubTask::class),
                'deleteComment' => auth()->user()->can('delete', Comment::class),
            ],
            'sub-task' => $subTask,
            'statuses' => Status::all(),
            'priorities' => Priority::all(),
            'users' => User::all(),
            'supervisorsAndAdmins' => User::getAllSupervisorsAndAdmins()->get(),
            'comments' => $comments,
            'tasks' => Task::all(),
        ]);
    }

    /**
     *  Edit a sub-task
     */
    public function edit(StoreSubTaskRequest $request, SubTask $subTask)
    {
        // check if user can edit sub-task
        Gate::authorize('edit', $subTask);

        // Get validated data
        $validated = $request->validated();

        // use db transaction
        $updatedSubTask = DB::transaction(function () use ($validated, $subTask) {
            // Update the task with validated data
            $subTask->update([
                ...$validated,
                'updated_by' => auth()->id(),
            ]);

            // Update assignees if they exist in the request
            if (isset($validated['assignees'])) {
                $subTask->assignees()->sync($validated['assignees']);
            }

            // Update viewers if they exist in the request
            if (isset($validated['viewers'])) {
                $subTask->viewers()->sync($validated['viewers']);
            }

            return $subTask;
        });

        // redirect to show page with flash message
        return to_route('sub-tasks.show', $updatedSubTask->id)
            ->with('flash', new FlashMessage(
                'Sub-task Updated Successfully',
                FlashMessageVariant::Success,
                FlashMessageType::Normal,
            )->toArray());
    }

    /**
     * Update status of the sub-task
     */
    public function updateStatus(Request $request, SubTask $subTask)
    {
        // check if user can update status of task
        Gate::authorize('updateStatus', $subTask);

        // Validate the incoming request data
        $validated = $request->validate([
            'status_id' => ['required', 'exists:statuses,id'],
        ]);

        // get the name of done status
        $doneStatusId = Status::where('name', 'Done')->first()->id;

        // Check if user is trying to set status to "done"
        if ($validated['status_id'] == $doneStatusId) {
            // check if user can update status to done
            Gate::authorize('updateStatusToDone', $subTask);
        }

        // Update the sub-task with the new status
        $subTask->update([
            'status_id' => $validated['status_id'],
        ]);

        // redirect to show page with flash message
        return to_route('sub-tasks.show', $subTask->id)
            ->with('flash', new FlashMessage(
                'Sub-task Updated Successfully',
                FlashMessageVariant::Success,
                FlashMessageType::Normal,
            )->toArray());
    }

    /**
     * Delete a sub-task
     */
    public function delete(SubTask $subTask)
    {
        // check if user can delete the task
        Gate::authorize('delete', $subTask);

        // delete the task
        $deleted = $subTask->delete();

        // if task deleted
        if ($deleted) {
            // redirect to show all page with success message
            return to_route('sub-tasks.show-all')
                ->with('flash', new FlashMessage(
                    'Sub-task Deleted Succesfully',
                    FlashMessageVariant::Success,
                    FlashMessageType::Normal,
                )->toArray());
        }

        // if not deleted then send back with flash message
        return back()
            ->with('flash', new FlashMessage(
                'There was a problem in deleting sub-task',
                FlashMessageVariant::Error,
                FlashMessageType::Normal,
            )->toArray());
    }

    /**
     * Add a comment in sub-task
     */
    public function createComment(Request $request, SubTask $subTask)
    {
        // check if user can create comment in sub-task
        Gate::authorize('createComment', $subTask);

        // validate the incoming request
        $validated = $request->validate([
            'content' => ['required', 'min:3'],
        ]);

        // create a comment
        $subTask->comments()->create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
        ]);

        // redirect to show page
        return to_route('sub-tasks.show', $subTask->id);
    }
}
