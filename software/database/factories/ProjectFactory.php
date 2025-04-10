<?php

namespace Database\Factories;

use App\Models\Priority;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
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

        return [
            'name' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'start_date' => now(),
            'due_date' => now()->addDays(30),
            'status_id' => $status->id,
            'priority_id' => $priority->id,
            'is_private' => false,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'supervisor_id' => $user->id,
        ];
    }

    /**
     * Indicate that the project is private.
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
     * Create a project with a specific supervisor.
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
