<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\StatusEnum;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Status;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // get user
        $user = auth()->user();
        // get all filters
        $search = $request->input('search');
        $statusIds = array_map('intval', $request->array('statusIds'));
        $priorityIds = array_map('intval', $request->array('priorityIds'));
        $supervisorIds = array_map('intval', $request->array('supervisorIds'));
        $creatorIds = array_map('intval', $request->array('creatorIds'));
        $assigneeIds = array_map('intval', $request->array('assigneeIds'));
        $viewerIds = array_map('intval', $request->array('viewerIds'));
        $perPage = $request->integer('perPage', 10);
        $showOverdue = $request->boolean('showOverdue', false);
        $sortBy = $request->input('sortBy', 'updated_at');
        $sortDirection = $request->input('sortDirection', 'asc');
        $taskTypes = $request->array('taskTypes');

        // validate sort by filter, it should only belong from these 4 values
        $allowedSortFields = ['due_date', 'updated_at', 'status_id', 'priority_id'];
        if (! in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'updated_at'; // Fallback to default if invalid
        }
        // validate sort direction
        $sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        $query = null;

        // to check if we need a certain task type
        $getProjects = empty($taskTypes) || in_array('project', $taskTypes);
        $getTasks = empty($taskTypes) || in_array('task', $taskTypes);
        $getSubTasks = empty($taskTypes) || in_array('sub-task', $taskTypes);

        // get projects and add to query
        if ($getProjects) {
            $projects = $this->getFilteredProjects($user, $search, $statusIds, $priorityIds, $supervisorIds, $creatorIds, $assigneeIds, $viewerIds, $showOverdue);
            $query = $projects;
        }

        // get tasks and add to query
        if ($getTasks) {
            $tasks = $this->getFilteredTasks($user, $search, $statusIds, $priorityIds, $supervisorIds, $creatorIds, $assigneeIds, $viewerIds, $showOverdue);
            $query = $query ? $query->union($tasks) : $tasks;
        }

        // get sub tasks and add to query
        if ($getSubTasks) {
            $subTasks = $this->getFilteredSubTasks($user, $search, $statusIds, $priorityIds, $supervisorIds, $creatorIds, $assigneeIds, $viewerIds, $showOverdue);
            $query = $query ? $query->union($subTasks) : $subTasks;
        }

        // get records by combining query and sorting and paginating the data
        $records = $query->orderBy($sortBy, $sortDirection)->paginate($perPage);

        return Inertia::render('Dashboard', [
            'tasks' => $records,
            'search' => $search,
            'supervisorsAndAdmins' => User::getAllSupervisorsAndAdmins()->get(),
            'users' => User::all(),
            'statuses' => Status::all(),
            'priorities' => Priority::all(),
            'defaultFilters' => [
                'search' => $search,
                'statusIds' => $statusIds,
                'priorityIds' => $priorityIds,
                'supervisorIds' => $supervisorIds,
                'creatorIds' => $creatorIds,
                'assigneeIds' => $assigneeIds,
                'viewerIds' => $viewerIds,
                'showOverdue' => $showOverdue,
                'perPage' => $perPage,
                'sortBy' => $sortBy,
                'sortDirection' => $sortDirection,
                'taskTypes' => $taskTypes,
            ],
        ]);
    }

    private function getFilteredProjects($user, $searchQuery, $statusIds, $priorityIds, $supervisorIds, $creatorIds, $assigneeIds, $viewerIds, $showOverdue)
    {
        $query = Project::with(['status', 'priority']);

        // apply access control for non-admin/supervisor users
        if (! $user->hasRole([UserRole::Admin, UserRole::Supervisor])) {
            $query->where(function ($q) use ($user) {
                $q->where('is_private', false)
                    ->orWhereHas('assignees', fn ($subquery) => $subquery->where('user_id', $user->id))
                    ->orWhereHas('viewers', fn ($subquery) => $subquery->where('user_id', $user->id));
            });
        }

        // apply various filters
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                    ->orWhere('slug', 'like', "%{$searchQuery}%");
            });
        }

        if (! empty($statusIds)) {
            $query->whereIn('status_id', $statusIds);
        }

        if (! empty($priorityIds)) {
            $query->whereIn('priority_id', $priorityIds);
        }

        if (! empty($supervisorIds)) {
            $query->whereIn('supervisor_id', $supervisorIds);
        }

        if (! empty($assigneeIds)) {
            $query->whereHas('assignees', function ($q) use ($assigneeIds) {
                $q->whereIn('user_id', $assigneeIds);
            });
        }

        if (! empty($viewerIds)) {
            $query->whereHas('viewers', function ($q) use ($viewerIds) {
                $q->whereIn('user_id', $viewerIds);
            });
        }

        if (! empty($creatorIds)) {
            $query->whereIn('created_by', $creatorIds);
        }

        // apply overdue filter - items with due_date in the past and not done
        // TODO: after adding cancelled fix this query
        if ($showOverdue) {
            $today = Carbon::now()->startOfDay();
            $query->where('due_date', '<', $today)
                ->whereHas('status', function ($q) {
                    $q->whereNotIn('name', [StatusEnum::Done]);
                });
        }

        return $query->select([
            'id',
            'name',
            'slug',
            'status_id',
            'status',
            'priority_id',
            'priority',
            'start_date',
            'due_date',
            'updated_at',
            DB::raw('NULL as project_id'),
            DB::raw('NULL as task_id'),
        ]);
    }

    // Similar functions for tasks and subtasks
    private function getFilteredTasks($user, $searchQuery, $statusFilter, $priorityFilter, $supervisorFilter, $creatorIds, $assigneeIds, $viewerIds, $showOverdue)
    {
        $query = Task::with(['status', 'priority']);

        // Access control
        if (! $user->hasRole([UserRole::Admin, UserRole::Supervisor])) {
            $query->where(function ($q) use ($user) {
                $q->where('is_private', false)
                    ->orWhereHas('assignees', fn ($subquery) => $subquery->where('user_id', $user->id))
                    ->orWhereHas('viewers', fn ($subquery) => $subquery->where('user_id', $user->id));
            });
        }

        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                    ->orWhere('slug', 'like', "%{$searchQuery}%");
            });
        }

        if (! empty($statusFilter)) {
            $query->whereIn('status_id', $statusFilter);
        }

        if (! empty($priorityFilter)) {
            $query->whereIn('priority_id', $priorityFilter);
        }

        if (! empty($supervisorFilter)) {
            $query->whereIn('supervisor_id', $supervisorFilter);
        }

        if (! empty($assigneeIds)) {
            $query->whereHas('assignees', function ($q) use ($assigneeIds) {
                $q->whereIn('user_id', $assigneeIds);
            });
        }

        if (! empty($viewerIds)) {
            $query->whereHas('viewers', function ($q) use ($viewerIds) {
                $q->whereIn('user_id', $viewerIds);
            });
        }

        if (! empty($creatorIds)) {
            $query->whereIn('created_by', $creatorIds);
        }

        // Apply overdue filter - items with due_date in the past and not done
        // TODO: after adding cancelled fix this query
        if ($showOverdue) {
            $today = Carbon::now()->startOfDay();
            $query->where('due_date', '<', $today)
                ->whereHas('status', function ($q) {
                    $q->whereNotIn('name', [StatusEnum::Done]);
                });
        }

        return $query->select([
            'id',
            'name',
            'slug',
            'status_id',
            'status',
            'priority_id',
            'priority',
            'start_date',
            'due_date',
            'updated_at',
            'project_id',
            DB::raw('NULL as task_id'),
        ]);
    }

    private function getFilteredSubTasks($user, $searchQuery, $statusFilter, $priorityFilter, $supervisorFilter, $creatorIds, $assigneeIds, $viewerIds, $showOverdue)
    {
        $query = SubTask::with(['status', 'priority']);

        // access control
        if (! $user->hasRole([UserRole::Admin, UserRole::Supervisor])) {
            $query->where(function ($q) use ($user) {
                $q->where('is_private', false)
                    ->orWhereHas('assignees', fn ($subquery) => $subquery->where('user_id', $user->id))
                    ->orWhereHas('viewers', fn ($subquery) => $subquery->where('user_id', $user->id));
            });
        }

        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                    ->orWhere('slug', 'like', "%{$searchQuery}%");
            });
        }

        if (! empty($statusFilter)) {
            $query->whereIn('status_id', $statusFilter);
        }

        if (! empty($priorityFilter)) {
            $query->whereIn('priority_id', $priorityFilter);
        }

        if (! empty($supervisorFilter)) {
            $query->whereIn('supervisor_id', $supervisorFilter);
        }

        if (! empty($assigneeIds)) {
            $query->whereHas('assignees', function ($q) use ($assigneeIds) {
                $q->whereIn('user_id', $assigneeIds);
            });
        }

        if (! empty($viewerIds)) {
            $query->whereHas('viewers', function ($q) use ($viewerIds) {
                $q->whereIn('user_id', $viewerIds);
            });
        }

        if (! empty($creatorIds)) {
            $query->whereIn('created_by', $creatorIds);
        }

        // Apply overdue filter - items with due_date in the past and not done
        // TODO: after adding cancelled fix this query
        if ($showOverdue) {
            $today = Carbon::now()->startOfDay();
            $query->where('due_date', '<', $today)
                ->whereHas('status', function ($q) {
                    $q->whereNotIn('name', [StatusEnum::Done]);
                });
        }

        return $query->select([
            'id',
            'name',
            'slug',
            'status_id',
            'status',
            'priority_id',
            'priority',
            'start_date',
            'due_date',
            'updated_at',
            DB::raw('NULL as project_id'),
            'task_id',
        ]);
    }
}
