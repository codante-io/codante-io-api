<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Lesson;
use App\Models\User;
use Database\Factories\LessonFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {


        \App\Models\User::factory(10)->create();
        \App\Models\Course::factory(10)->has(Lesson::factory()->count(4))->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $user = new User([
            'name' => 'Roberto Cestari',
            'email' => 'robertotcestari@gmail.com',
            'password' => bcrypt('19881988'),
        ]);

        $user->save();
    }
}
