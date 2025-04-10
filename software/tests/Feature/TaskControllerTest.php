<?php

use App\Enums\UserRole;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'StatusSeeder']);
    Artisan::call('db:seed', ['--class' => 'PrioritySeeder']);
});

test('admin can view create task screen', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);

    $this->actingAs($admin);

    $response = $this->get(route('tasks.create'));

    $response->assertSuccessful();
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('task/CreateTask')
    );
});

test('supervisor can view create task screen', function () {
    $supervisor = User::factory()->create(['role' => UserRole::Supervisor->value]);

    $this->actingAs($supervisor);

    $response = $this->get(route('tasks.create'));

    $response->assertSuccessful();
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('task/CreateTask')
    );
});

test('regular user cannot view create task screen', function () {
    $regularUser = User::factory()->create(['role' => UserRole::Staff->value]);

    $this->actingAs($regularUser);

    $response = $this->get(route('tasks.create'));

    $response->assertForbidden();
});

test('admin can update a task', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $project = Project::factory()->create();
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'name' => 'Original Task',
        'description' => 'Original description',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($admin);

    $response = $this->put(route('tasks.edit', $task), [
        ...$task->toArray(),
        'name' => 'Updated Task',
        'description' => 'Updated by admin',
        'status_id' => Status::find(2)->first()->id,
        'priority_id' => Priority::find(2)->first()->id,
        'assignees' => [User::first()->id],
    ]);

    $response->assertRedirect(route('tasks.show', $task));
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'name' => 'Updated Task',
        'description' => 'Updated by admin',
    ]);
});

test('supervisor can update a task', function () {
    $supervisor = User::factory()->create(['role' => UserRole::Supervisor->value]);
    $project = Project::factory()->create();
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'name' => 'Original Task',
        'description' => 'Original description',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($supervisor);

    $response = $this->put(route('tasks.edit', $task), [
        ...$task->toArray(),
        'name' => 'Supervisor Updated',
        'description' => 'Updated by supervisor',
        'status_id' => Status::find(2)->first()->id,
        'priority_id' => Priority::find(2)->first()->id,
        'assignees' => [User::first()->id],
    ]);

    $response->assertRedirect(route('tasks.show', $task));
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'name' => 'Supervisor Updated',
        'description' => 'Updated by supervisor',
    ]);
});

test('staff cannot update a task', function () {
    $staff = User::factory()->create(['role' => UserRole::Staff->value]);
    $project = Project::factory()->create();
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'name' => 'Original Task',
        'description' => 'Original description',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($staff);

    $response = $this->put(route('tasks.edit', $task), [
        ...$task->toArray(),
        'name' => 'Staff Attempt',
        'description' => 'Update attempt by staff',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
        'assignees' => [User::first()->id],
    ]);

    $response->assertForbidden();
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'name' => 'Original Task',
        'description' => 'Original description',
    ]);
});

test('admin can delete a task', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $project = Project::factory()->create();
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'name' => 'Task To Delete',
        'description' => 'This task will be deleted',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($admin);

    $response = $this->delete(route('tasks.delete', $task));

    $response->assertRedirect(route('tasks.show-all'));
    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id,
    ]);
});

test('supervisor cannot delete a task', function () {
    $supervisor = User::factory()->create(['role' => UserRole::Supervisor->value]);
    $project = Project::factory()->create();
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'name' => 'Task Not For Deletion',
        'description' => 'This task should not be deleted by supervisor',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($supervisor);

    $response = $this->delete(route('tasks.delete', $task));

    $response->assertForbidden();
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'name' => 'Task Not For Deletion',
    ]);
});

test('staff cannot delete a task', function () {
    $staff = User::factory()->create(['role' => UserRole::Staff->value]);
    $project = Project::factory()->create();
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'name' => 'Task For Staff',
        'description' => 'This task should not be deleted by staff',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($staff);

    $response = $this->delete(route('tasks.delete', $task));

    $response->assertForbidden();
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'name' => 'Task For Staff',
    ]);
});
