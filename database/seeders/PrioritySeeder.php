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

        foreach ($priorities as $priority) {
            Priority::firstOrCreate(
                ['name' => $priority['name']],
                $priority
            );
        }
    }
}
