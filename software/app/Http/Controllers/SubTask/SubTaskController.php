<?php

namespace App\Http\Controllers\SubTask;

use App\Enums\FlashMessageType;
use App\Enums\FlashMessageVariant;
use App\Helpers\FlashMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubTask\StoreSubTaskRequest;
use App\Models\Priority;
use App\Models\Status;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\User;
use DB;
use Gate;
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
            $subTask->slug = 'SUB-' . $subTask->id;
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
}
