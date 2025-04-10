<?php

namespace Database\Factories;

use App\Models\Priority;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // Ensure we have necessary related models
        $status = Status::first();
        $priority = Priority::first();
        $user = User::first();
        $project = Project::first() ?? Project::factory()->create();

        return [
            'project_id' => $project->id,
            'name' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'start_date' => now(),
            'due_date' => now()->addDays(15),
            'status_id' => $status->id,
            'priority_id' => $priority->id,
            'is_private' => false,
            'supervisor_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ];
    }

    /**
     * Indicate that the task is private.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function private()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_private' => true,
            ];
        });
    }

    /**
     * Create a task with a specific project.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function forProject(Project $project)
    {
        return $this->state(function (array $attributes) use ($project) {
            return [
                'project_id' => $project->id,
            ];
        });
    }
}
