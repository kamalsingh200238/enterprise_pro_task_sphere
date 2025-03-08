<?php

namespace App\Http\Controllers\Task;

use App\Enums\FlashMessageType;
use App\Enums\FlashMessageVariant;
use App\Helpers\FlashMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use DB;
use Gate;
use Inertia\Inertia;

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

        // show the create project form to user
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
        // check if user can create a project
        Gate::authorize('create', Task::class);

        // get the validated data from request
        $validated = $request->validated();

        // use db transaction to create a task
        $task = DB::transaction(function () use ($validated) {
            // create a new project
            $task = Task::create(
                [
                    ...$validated,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]
            );

            // add slug in the project
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

            // return project
            return $task;
        });

        // return to show all page with a success flash message
        return to_route('tasks.show-all')
            ->with('flash', new FlashMessage(
                'Created Task Successfully',
                FlashMessageVariant::Success,
                FlashMessageType::CreatedTask,
                ['task' => $task]
            )->toArray());
    }
}
