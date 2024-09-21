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

        User::factory()->create([
            'id' => 1,
            'name' => 'Seeded User',
            'email' => 'seeded@example.com',
            'password' => bcrypt('pass123.')
        ]);

        Note::factory(100)->create(['user_id' => 1]);

        // User::factory()->create([
        //     'id' => 2,
        //     'name' => 'Test User(1)',
        //     'email' => 'test1@example.com',
        //     'password' => bcrypt('pass123.')
        // ]);
        // Note::factory(100)->create(['user_id' => 2]);
    }
}
