<?php

namespace App\Http\Controllers;

use App\Helpers\FlashMessage;
use App\Http\Requests\StoreProjectRequest;
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
     * Display a listing of the resource.
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
            'supervisorsAndAdmins' => User::getAllSupervisorAndAdmins(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        // check if user can create a project
        Gate::authorize('create', Project::class);

        // get the validated data from request
        $validated = $request->validated();

        $project = DB::transaction(function () use ($validated) {
            $project = Project::create(
                [
                    ...$validated,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]
            );

            $project->slug = 'PROJECT-' . $project->id;
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

        return to_route('projects.show-all')->with('created', $project);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $project = Project::with(['assignees:id', 'viewers:id'])->findOrFail($id);

        Gate::authorize('view', arguments: $project);

        return Inertia::render('Project/ShowProject', [
            'can' => [
                'edit' => auth()->user()->can('edit', $project),
                'updateStatus' => auth()->user()->can('updateStatus', $project),
                'updateStatusToDone' => auth()->user()->can('updateStatusToDone', Project::class),
            ],
            'project' => $project,
            'statuses' => Status::all(),
            'priorities' => Priority::all(),
            'users' => User::all(),
            'supervisorsAndAdmins' => User::getAllSupervisorAndAdmins(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StoreProjectRequest $request, Project $project)
    {
        Gate::authorize('edit', $project);

        // Get validated data
        $validated = $request->validated();

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

        return to_route('projects.show', $updatedProject->id)
            ->with('flash', new FlashMessage('Project updated successfully', '', 'success')->toArray());
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateStatus(Request $request, Project $project)
    {
        Gate::authorize('updateStatus', $project);

        // Validate the incoming request data
        $validated = $request->validate([
            'status_id' => ['required', 'exists:statuses,id'],
        ]);

        $doneStatusId = Status::where('name', 'Done')->first()->id;

        // Check if user is trying to set status to "done"
        if ($validated['status_id'] == $doneStatusId) {
            Gate::authorize('updateStatusToDone', $project);
        }

        // Update the project with the new status
        $project->update([
            'status_id' => $validated['status_id'],
        ]);

        return to_route('projects.show', $project->id)
            ->with('flash', new FlashMessage('Project updated successfully', '', 'success')->toArray());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Project $project)
    {
        Gate::authorize('delete', $project);
        $deleted = $project->delete();
        if ($deleted) {
            return to_route('projects.show-all')
                ->with('deleted', $project);
        }
        return back()->with('flash', new FlashMessage('There was a problem in deleting project', '', 'danger')->toArray());
    }
}
