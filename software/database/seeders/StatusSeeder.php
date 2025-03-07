<?php

namespace Database\Seeders;

use App\Enums\Color;
use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seeds the 'statuses' table with default status values.
        $statuses = [
            [
                'name' => 'Backlog',
                'color' => Color::Gray,
            ],
            [
                'name' => 'In Progress',
                'color' => Color::Blue,
            ],
            [
                'name' => 'On Hold',
                'color' => Color::Gray,
            ],
            [
                'name' => 'In Review',
                'color' => Color::Green,
            ],
            [
                'name' => 'Done',
                'color' => Color::Green,
            ],
        ];

        // Insert statuses if they do not already exist
        foreach ($statuses as $status) {
            Status::firstOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
    }
}
