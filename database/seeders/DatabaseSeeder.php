<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\Instructor;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(10)->create();
        \App\Models\Category::factory(10)->create();
        \App\Models\Instructor::factory(10)->create();
        \App\Models\Workshop::factory(10)->has(Lesson::factory()->count(4))->has(Category::factory()->count(3))->create();



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

        $user2 = new User([
            'name' => 'Icaro Harry',
            'email' => 'icaropc17@gmail.com',
            'password' => bcrypt('Codante2023'),
        ]);

        $user2->save();
    }
}
