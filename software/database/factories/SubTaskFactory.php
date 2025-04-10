<?php

namespace Database\Factories;

use App\Models\Priority;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subtask>
 */
class SubTaskFactory extends Factory
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
        $task = Task::first() ?? Task::factory()->create();

        return [
            'task_id' => $task->id,
            'name' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'start_date' => now(),
            'due_date' => now()->addDays(7),
            'status_id' => $status->id,
            'priority_id' => $priority->id,
            'is_private' => false,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'supervisor_id' => $user->id,
        ];
    }

    /**
     * Indicate that the subtask is private.
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
     * Create a subtask with a specific task.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function forTask(Task $task)
    {
        return $this->state(function (array $attributes) use ($task) {
            return [
                'task_id' => $task->id,
            ];
        });
    }

    /**
     * Create a subtask with a specific supervisor.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withSupervisor(User $supervisor)
    {
        return $this->state(function (array $attributes) use ($supervisor) {
            return [
                'supervisor_id' => $supervisor->id,
            ];
        });
    }
}
