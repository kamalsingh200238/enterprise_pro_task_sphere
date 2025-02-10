<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // TODO: remove all the code that generates dummy members
        // create admin
        User::factory()->admin()->create(
            ['name' => 'Kamal Singh', 'email' => 'kamal.singh@example.com'],
        );

        // Create supervisors
        $supervisors = [
            'Oshan Jain',
            'Md. Fahim',
            'Daniel Amadike',
            'Maham Mahmood',
            'Sabrina Mei',
            'Suhaana Hussain',
        ];

        foreach ($supervisors as $index => $name) {
            User::factory()->supervisor()->create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)).'@example.com',
            ]);
        }

        // create 10 dummy staff members
        User::factory(10)->create();
    }
}
