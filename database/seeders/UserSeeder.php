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

        // create admins
        $admins = [
            'Admin',
            'Kamal Singh',
        ];
        foreach ($admins as $admin) {
            User::factory()->admin()->create([
                'name' => $admin,
                'email' => strtolower(str_replace(' ', '.', $admin)).'@example.com',
            ]);
        }

        // Create supervisors
        $supervisors = [
            'Oshan Jain',
            'Md. Fahim',
            'Daniel Amadike',
            'Maham Mahmood',
            'Sabrina Mei',
            'Suhaana Hussain',
            'Supervisor',
        ];
        foreach ($supervisors as $supervisor) {
            User::factory()->supervisor()->create([
                'name' => $supervisor,
                'email' => strtolower(str_replace(' ', '.', $supervisor)).'@example.com',
            ]);
        }

        // create staff members
        $staffs = [
            'Staff',
        ];
        foreach ($staffs as $staff) {
            User::factory()->create([
                'name' => $staff,
                'email' => strtolower(str_replace(' ', '.', $staff)).'@example.com',
            ]);
        }
        User::factory(10)->create();
    }
}
