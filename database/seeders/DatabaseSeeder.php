<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('pass123.')
        ]);

        Note::factory(2)->create(['user_id' => 1]);

        // User::factory()->create([
        //     'id' => 2,
        //     'name' => 'Test User(1)',
        //     'email' => 'test1@example.com',
        //     'password' => bcrypt('pass123.')
        // ]);
        // Note::factory(100)->create(['user_id' => 2]);
    }
}
