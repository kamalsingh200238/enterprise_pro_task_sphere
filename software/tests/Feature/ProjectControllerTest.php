<?php

use App\Enums\UserRole;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'StatusSeeder']);
    Artisan::call('db:seed', ['--class' => 'PrioritySeeder']);
});

test('admin can view create project screen', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);

    $this->actingAs($admin);

    $response = $this->get(route('projects.create'));

    $response->assertSuccessful();
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('project/CreateProject')
    );
});

test('supervisor can view create project screen', function () {
    $supervisor = User::factory()->create(['role' => UserRole::Supervisor->value]);

    $this->actingAs($supervisor);

    $response = $this->get(route('projects.create'));

    $response->assertSuccessful();
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('project/CreateProject')
    );
});

test('staff user cannot view create project screen', function () {
    $regularUser = User::factory()->create(['role' => UserRole::Staff->value]);

    $this->actingAs($regularUser);

    $response = $this->get(route('projects.create'));

    $response->assertForbidden();
});

test('admin can update a project', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $project = Project::factory()->create([
        'name' => 'Original Project',
        'description' => 'Original description',
        'status_id' => Status::first(),
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($admin);

    $response = $this->put(route('projects.edit', $project), [
        ...$project->toArray(),
        'name' => 'Updated Project',
        'description' => 'Updated by admin',
        'status_id' => Status::find(2)->first()->id,
        'priority_id' => Priority::find(2)->first()->id,
        'assignees' => [User::first()->id],
    ]);

    $response->assertRedirect(route('projects.show', $project));
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Updated Project',
        'description' => 'Updated by admin',
    ]);
});

test('supervisor can update a project', function () {
    $supervisor = User::factory()->create(['role' => UserRole::Supervisor->value]);
    $project = Project::factory()->create([
        'name' => 'Original Project',
        'description' => 'Original description',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($supervisor);

    $response = $this->put(route('projects.edit', $project), [
        ...$project->toArray(),
        'name' => 'Supervisor Updated',
        'description' => 'Updated by supervisor',
        'status_id' => Status::find(2)->first()->id,
        'priority_id' => Priority::find(2)->first()->id,
        'assignees' => [User::first()->id],
    ]);

    $response->assertRedirect(route('projects.show', $project));
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Supervisor Updated',
        'description' => 'Updated by supervisor',
    ]);
});

test('staff cannot update a project', function () {
    $staff = User::factory()->create(['role' => UserRole::Staff->value]);
    $project = Project::factory()->create([
        'name' => 'Original Project',
        'description' => 'Original description',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($staff);

    $response = $this->put(route('projects.edit', $project), [
        ...$project->toArray(),
        'name' => 'Staff Attempt',
        'description' => 'Update attempt by staff',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
        'assignees' => [User::first()->id],
    ]);

    $response->assertForbidden();
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Original Project',
        'description' => 'Original description',
    ]);
});

test('admin can delete a project', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $project = Project::factory()->create([
        'name' => 'Project To Delete',
        'description' => 'This project will be deleted',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($admin);

    $response = $this->delete(route('projects.delete', $project));

    $response->assertRedirect(route('projects.show-all'));
    $this->assertDatabaseMissing('projects', [
        'id' => $project->id,
    ]);
});

test('supervisor cannot delete a project', function () {
    $supervisor = User::factory()->create(['role' => UserRole::Supervisor->value]);
    $project = Project::factory()->create([
        'name' => 'Project Not For Deletion',
        'description' => 'This project should not be deleted by supervisor',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($supervisor);

    $response = $this->delete(route('projects.delete', $project));

    $response->assertForbidden();
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Project Not For Deletion',
    ]);
});

test('staff cannot delete a project', function () {
    $staff = User::factory()->create(['role' => UserRole::Staff->value]);
    $project = Project::factory()->create([
        'name' => 'Project For Staff',
        'description' => 'This project should not be deleted by staff',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($staff);

    $response = $this->delete(route('projects.delete', $project));

    $response->assertForbidden();
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Project For Staff',
    ]);
});
