<?php

namespace Database\Seeders;

use App\Enums\Color;
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
                'color' => Color::Gray,
            ],
            [
                'name' => 'Medium',
                'color' => Color::Blue,
            ],
            [
                'name' => 'High',
                'color' => Color::Yellow,
            ],
            [
                'name' => 'Urgent',
                'color' => Color::Red,
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
