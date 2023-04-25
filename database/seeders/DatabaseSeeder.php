<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Challenge;
use App\Models\Lesson;
use App\Models\Tag;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(10)->create();
        \App\Models\Tag::factory(10)->create();
        \App\Models\Instructor::factory(10)->create();
        \App\Models\Workshop::factory(10)->has(Lesson::factory()->count(4))->has(Tag::factory()->count(3))->set('is_standalone', true)->create();
        \App\Models\Challenge::factory(10)
            ->has(
                Workshop::factory()
                    ->set('is_standalone', false)
                    ->count(1)
                    ->has(Lesson::factory()->count(4))
            )
            ->has(Tag::factory()->count(3))
            ->create();

        \App\Models\Track::factory(3)
            ->hasAttached(
                Workshop::factory()
                    ->count(3)
                    ->has(Lesson::factory()->count(4))
                    ->has(Tag::factory()->count(3))
                    ->set('is_standalone', true),
                fn () => ['position' => fake()->randomFloat(4, 1, 10)]
            )
            ->hasAttached(
                Challenge::factory()
                    ->count(3)
                    ->has(
                        Workshop::factory()
                            ->set('is_standalone', false)
                            ->count(1)
                            ->has(Lesson::factory()->count(4))
                    )
                    ->has(Tag::factory()->count(3)),
                fn () => ['position' => fake()->randomFloat(4, 1, 10)]
            )
            ->create();

        \App\Models\User::factory(3)
            ->hasAttached(
                \App\Models\Challenge::factory(10)
                    ->has(
                        Tag::factory()->count(3)
                    ), fn () => ['completed' => fake()->boolean(), 'joined_discord' => fake()->boolean()]
            )
            ->create();




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
