<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create demo admin user
        User::factory()->create([
            'name' => 'Demo Admin',
            'email' => 'admin@larable.test',
        ]);

        // Create 10 sample users
        User::factory(10)->create();
    }
}
