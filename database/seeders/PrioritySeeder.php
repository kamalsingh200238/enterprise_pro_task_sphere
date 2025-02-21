<?php

namespace Database\Seeders;

use App\Color;
use App\Models\Priority;
use Illuminate\Database\Seeder;

class PrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seeds the 'priorities' table with default priority levels.
        $priorities = [
            [
                'name' => 'Low',
                'color' => Color::GRAY,
            ],
            [
                'name' => 'Medium',
                'color' => Color::BLUE,
            ],
            [
                'name' => 'High',
                'color' => Color::YELLOW,
            ],
            [
                'name' => 'Urgent',
                'color' => Color::RED,
            ],
        ];

        // Insert priorities if they do not already exist
        foreach ($priorities as $priority) {
            Priority::firstOrCreate(
                ['name' => $priority['name']],
                $priority
            );
        }
    }
}
