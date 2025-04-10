<?php

use App\Enums\UserRole;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Status;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'StatusSeeder']);
    Artisan::call('db:seed', ['--class' => 'PrioritySeeder']);
});

test('admin can view create subtask screen', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);

    $this->actingAs($admin);

    $response = $this->get(route('sub-tasks.create'));

    $response->assertSuccessful();
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('sub-task/CreateSubTask')
    );
});

test('supervisor can view create subtask screen', function () {
    $supervisor = User::factory()->create(['role' => UserRole::Supervisor->value]);

    $this->actingAs($supervisor);

    $response = $this->get(route('sub-tasks.create'));

    $response->assertSuccessful();
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('sub-task/CreateSubTask')
    );
});

test('regular user cannot view create subtask screen', function () {
    $regularUser = User::factory()->create(['role' => UserRole::Staff->value]);

    $this->actingAs($regularUser);

    $response = $this->get(route('sub-tasks.create'));

    $response->assertForbidden();
});

test('admin can update a subtask', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id]);
    $subtask = SubTask::factory()->create([
        'task_id' => $task->id,
        'name' => 'Original SubTask',
        'description' => 'Original description',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($admin);

    $response = $this->put(route('sub-tasks.edit', $subtask), [
        ...$subtask->toArray(),
        'name' => 'Updated SubTask',
        'description' => 'Updated by admin',
        'status_id' => Status::find(2)->first()->id,
        'priority_id' => Priority::find(2)->first()->id,
        'assignees' => [User::first()->id],
    ]);

    $response->assertRedirect(route('sub-tasks.show', $subtask));
    $this->assertDatabaseHas('sub_tasks', [
        'id' => $subtask->id,
        'name' => 'Updated SubTask',
        'description' => 'Updated by admin',
    ]);
});

test('supervisor can update a subtask', function () {
    $supervisor = User::factory()->create(['role' => UserRole::Supervisor->value]);
    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id]);
    $subtask = SubTask::factory()->create([
        'task_id' => $task->id,
        'name' => 'Original SubTask',
        'description' => 'Original description',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($supervisor);

    $response = $this->put(route('sub-tasks.edit', $subtask), [
        ...$subtask->toArray(),
        'name' => 'Supervisor Updated',
        'description' => 'Updated by supervisor',
        'status_id' => Status::find(2)->first()->id,
        'priority_id' => Priority::find(2)->first()->id,
        'assignees' => [User::first()->id],
    ]);

    $response->assertRedirect(route('sub-tasks.show', $subtask));
    $this->assertDatabaseHas('sub_tasks', [
        'id' => $subtask->id,
        'name' => 'Supervisor Updated',
        'description' => 'Updated by supervisor',
    ]);
});

test('staff cannot update a subtask', function () {
    $staff = User::factory()->create(['role' => UserRole::Staff->value]);
    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id]);
    $subtask = SubTask::factory()->create([
        'task_id' => $task->id,
        'name' => 'Original SubTask',
        'description' => 'Original description',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($staff);

    $response = $this->put(route('sub-tasks.edit', $subtask), [
        ...$subtask->toArray(),
        'name' => 'Staff Attempt',
        'description' => 'Update attempt by staff',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
        'assignees' => [User::first()->id],
    ]);

    $response->assertForbidden();
    $this->assertDatabaseHas('sub_tasks', [
        'id' => $subtask->id,
        'name' => 'Original SubTask',
        'description' => 'Original description',
    ]);
});

test('admin can delete a subtask', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id]);
    $subtask = SubTask::factory()->create([
        'task_id' => $task->id,
        'name' => 'SubTask To Delete',
        'description' => 'This subtask will be deleted',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($admin);

    $response = $this->delete(route('sub-tasks.delete', $subtask));

    $response->assertRedirect(route('sub-tasks.show-all'));
    $this->assertDatabaseMissing('sub_tasks', [
        'id' => $subtask->id,
    ]);
});

test('supervisor cannot delete a subtask', function () {
    $supervisor = User::factory()->create(['role' => UserRole::Supervisor->value]);
    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id]);
    $subtask = SubTask::factory()->create([
        'task_id' => $task->id,
        'name' => 'SubTask Not For Deletion',
        'description' => 'This subtask should not be deleted by supervisor',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($supervisor);

    $response = $this->delete(route('sub-tasks.delete', $subtask));

    $response->assertForbidden();
    $this->assertDatabaseHas('sub_tasks', [
        'id' => $subtask->id,
        'name' => 'SubTask Not For Deletion',
    ]);
});

test('staff cannot delete a subtask', function () {
    $staff = User::factory()->create(['role' => UserRole::Staff->value]);
    $project = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $project->id]);
    $subtask = SubTask::factory()->create([
        'task_id' => $task->id,
        'name' => 'SubTask For Staff',
        'description' => 'This subtask should not be deleted by staff',
        'status_id' => Status::first()->id,
        'priority_id' => Priority::first()->id,
    ]);

    $this->actingAs($staff);

    $response = $this->delete(route('sub-tasks.delete', $subtask));

    $response->assertForbidden();
    $this->assertDatabaseHas('sub_tasks', [
        'id' => $subtask->id,
        'name' => 'SubTask For Staff',
    ]);
});
