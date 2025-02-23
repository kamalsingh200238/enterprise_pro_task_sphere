<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use DB;
use Gate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Str;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

        try {
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

                if (isset($validated['assignees'])) {
                    $project->assignees()->attach($validated['assignees']);
                }

                if (isset($validated['viewers'])) {
                    $project->viewers()->attach($validated['viewers']);
                }

                return $project;
            });
            return to_route('projects.create')->with('message', ["value" => 'completely changed value: ' . $project->slug, 'duration' => 5000]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create project',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
