<?php

namespace Database\Seeders;

use App\Models\OAuthStatus;
use Illuminate\Database\Seeder;

class OAuthStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OAuthStatus::firstOrCreate(
            ['id' => 1],
            ['enabled' => true]
        );
    }
}
