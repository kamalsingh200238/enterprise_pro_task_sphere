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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $search = $request->input('search');
        $statusFilter = $request->input('status_ids', []);
        $priorityFilter = $request->input('priority_ids', []);
        $supervisorFilter = $request->input('supervisor_ids', []);
        $creatorFilter = request('creator_ids', []);
        $assigneeFilter = request('assignee_id', []);
        $viewerFilter = request('viewer_id', []);
        $perPage = request('per_page', 10);
        $overdueFilter = request('overdue', false);

        $sortBy = $request->input('sort_by', 'updated_at'); // Default sort by due_date
        $sortDirection = $request->input('sort_direction', 'asc'); // Default ascending

        // Validate sort field
        $allowedSortFields = ['due_date', 'updated_at', 'status_id', 'priority_id'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'due_date'; // Fallback to default if invalid
        }

        // Validate sort direction
        $sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        // Get filtered data from each model
        $projects = $this->getFilteredProjects($user, $search, $statusFilter, $priorityFilter, $supervisorFilter, $creatorFilter, $assigneeFilter, $viewerFilter, $overdueFilter);
        $tasks = $this->getFilteredTasks($user, $search, $statusFilter, $priorityFilter, $supervisorFilter, $creatorFilter, $assigneeFilter, $viewerFilter, $overdueFilter);
        $subTasks = $this->getFilteredSubTasks($user, $search, $statusFilter, $priorityFilter, $supervisorFilter, $creatorFilter, $assigneeFilter, $viewerFilter, $overdueFilter);

        $temp = $projects->union($tasks)->union($subTasks)->orderBy($sortBy, $sortDirection)->paginate($perPage);

        return Inertia::render("Dashboard", [
            'tasks' => $temp,
            'search' => $search,
            'supervisorsAndAdmins' => User::getAllSupervisorsAndAdmins()->get(),
            'users' => User::all(),
            'statuses' => Status::all(),
            'priorities' => Priority::all(),
        ]);
    }

    private function getFilteredProjects($user, $searchQuery, $statusFilter, $priorityFilter, $supervisorFilter, $creatorFilter, $assigneeFilter, $viewerFilter, $overdueFilter)
    {
        $query = Project::with(['status', 'priority']);

        // Apply access control for non-admin/supervisor users
        if (!$user->hasRole([UserRole::Admin, UserRole::Supervisor])) {
            $query->where(function ($q) use ($user) {
                $q->where('is_private', false)
                    ->orWhereHas('assignees', fn($subquery) => $subquery->where('user_id', $user->id))
                    ->orWhereHas('viewers', fn($subquery) => $subquery->where('user_id', $user->id));
            });
        }

        // Apply various filters
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                    ->orWhere('slug', 'like', "%{$searchQuery}%");
            });
        }

        if (!empty($statusFilter)) {
            $query->whereIn('status_id', $statusFilter);
        }

        if (!empty($priorityFilter)) {
            $query->whereIn('priority_id', $priorityFilter);
        }

        if (!empty($supervisorFilter)) {
            $query->whereIn('supervisor_id', $supervisorFilter);
        }

        if (!empty($assigneeFilter)) {
            $query->whereHas('assignees', function ($q) use ($assigneeFilter) {
                $q->whereIn('user_id', $assigneeFilter);
            });
        }

        if (!empty($viewerFilter)) {
            $query->whereHas('viewers', function ($q) use ($viewerFilter) {
                $q->whereIn('user_id', $viewerFilter);
            });
        }

        if (!empty($creatorFilter)) {
            $query->whereIn('created_by', $creatorFilter);
        }

        // Apply overdue filter - items with due_date in the past and not done/cancelled
        if ($overdueFilter) {
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
            'updated_at',
            DB::raw('NULL as project_id'),
            DB::raw('NULL as task_id')
        ]);
    }

    // Similar functions for tasks and subtasks
    private function getFilteredTasks($user, $searchQuery, $statusFilter, $priorityFilter, $supervisorFilter, $creatorFilter, $assigneeFilter, $viewerFilter, $overdueFilter)
    {
        $query = Task::with(['status', 'priority']);

        // Access control
        if (!$user->hasRole([UserRole::Admin, UserRole::Supervisor])) {
            $query->where(function ($q) use ($user) {
                $q->where('is_private', false)
                    ->orWhereHas('assignees', fn($subquery) => $subquery->where('user_id', $user->id))
                    ->orWhereHas('viewers', fn($subquery) => $subquery->where('user_id', $user->id));
            });
        }

        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                    ->orWhere('slug', 'like', "%{$searchQuery}%");
            });
        }

        if (!empty($statusFilter)) {
            $query->whereIn('status_id', $statusFilter);
        }

        if (!empty($priorityFilter)) {
            $query->whereIn('priority_id', $priorityFilter);
        }

        if (!empty($supervisorFilter)) {
            $query->whereIn('supervisor_id', $supervisorFilter);
        }

        if (!empty($assigneeFilter)) {
            $query->whereHas('assignees', function ($q) use ($assigneeFilter) {
                $q->whereIn('user_id', $assigneeFilter);
            });
        }

        if (!empty($viewerFilter)) {
            $query->whereHas('viewers', function ($q) use ($viewerFilter) {
                $q->whereIn('user_id', $viewerFilter);
            });
        }

        if (!empty($creatorFilter)) {
            $query->whereIn('created_by', $creatorFilter);
        }

        // Apply overdue filter - items with due_date in the past and not done/cancelled
        if ($overdueFilter) {
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
            'updated_at',
            'project_id',
            DB::raw('NULL as task_id')
        ]);
    }

    private function getFilteredSubTasks($user, $searchQuery, $statusFilter, $priorityFilter, $supervisorFilter, $creatorFilter, $assigneeFilter, $viewerFilter, $overdueFilter)
    {
        $query = SubTask::with(['status', 'priority']);

        // Access control
        if (!$user->hasRole([UserRole::Admin, UserRole::Supervisor])) {
            $query->where(function ($q) use ($user) {
                $q->where('is_private', false)
                    ->orWhereHas('assignees', fn($subquery) => $subquery->where('user_id', $user->id))
                    ->orWhereHas('viewers', fn($subquery) => $subquery->where('user_id', $user->id));
            });
        }

        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                    ->orWhere('slug', 'like', "%{$searchQuery}%");
            });
        }

        if (!empty($statusFilter)) {
            $query->whereIn('status_id', $statusFilter);
        }

        if (!empty($priorityFilter)) {
            $query->whereIn('priority_id', $priorityFilter);
        }

        if (!empty($supervisorFilter)) {
            $query->whereIn('supervisor_id', $supervisorFilter);
        }

        if (!empty($assigneeFilter)) {
            $query->whereHas('assignees', function ($q) use ($assigneeFilter) {
                $q->whereIn('user_id', $assigneeFilter);
            });
        }

        if (!empty($viewerFilter)) {
            $query->whereHas('viewers', function ($q) use ($viewerFilter) {
                $q->whereIn('user_id', $viewerFilter);
            });
        }

        if (!empty($creatorFilter)) {
            $query->whereIn('created_by', $creatorFilter);
        }

        // Apply overdue filter - items with due_date in the past and not done/cancelled
        if ($overdueFilter) {
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
            'updated_at',
            DB::raw('NULL as project_id'),
            'task_id'
        ]);
    }
}
