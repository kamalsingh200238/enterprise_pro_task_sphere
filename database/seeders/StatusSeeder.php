<?php

namespace Database\Seeders;

use App\Color;
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
                'color' => Color::GRAY,
            ],
            [
                'name' => 'In Progress',
                'color' => Color::BLUE,
            ],
            [
                'name' => 'On Hold',
                'color' => Color::GRAY,
            ],
            [
                'name' => 'In Review',
                'color' => Color::GREEN,
            ],
            [
                'name' => 'Done',
                'color' => Color::GREEN,
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
