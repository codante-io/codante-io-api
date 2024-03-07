<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\BlogPost;
use App\Models\Challenge;
use App\Models\Lesson;
use App\Models\Reaction;
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
        \App\Models\Workshop::factory(10)
            ->has(Lesson::factory()->count(4))
            ->has(Tag::factory()->count(3))
            ->set("is_standalone", true)
            ->set("featured", null)
            ->create();
        \App\Models\Workshop::factory(2)
            ->has(Lesson::factory()->count(4))
            ->has(Tag::factory()->count(3))
            ->set("is_standalone", true)
            ->set("featured", "landing")
            ->set("status", "published")
            ->create();

        \App\Models\Challenge::factory(4)
            ->has(
                Workshop::factory()
                    ->set("is_standalone", false)
                    ->set("featured", null)
                    ->count(1)
                    ->has(Lesson::factory()->count(4))
            )
            ->hasAttached(
                \App\Models\User::factory(23)->set(
                    "avatar_url",
                    "https://i.pravatar.cc/300"
                ),
                fn() => [
                    "completed" => fake()->boolean(),
                    "joined_discord" => fake()->boolean(),
                ]
            )
            ->has(Tag::factory()->count(3))
            ->set("featured", null)
            ->set("position", fake()->randomFloat(4, 1, 10))
            ->create();

        \App\Models\Challenge::factory(12)
            ->has(
                Workshop::factory()
                    ->set("is_standalone", false)
                    ->set("featured", null)
                    ->count(1)
                    ->has(Lesson::factory()->count(4))
            )
            ->hasAttached(
                \App\Models\User::factory(23)->set(
                    "avatar_url",
                    "https://i.pravatar.cc/300"
                ),
                fn() => [
                    "completed" => fake()->boolean(),
                    "joined_discord" => fake()->boolean(),
                ]
            )
            ->has(Tag::factory()->count(3))
            ->set("featured", "landing")
            ->set("position", fake()->randomFloat(4, 1, 10))
            ->create();

        \App\Models\Track::factory(3)
            ->hasAttached(
                Workshop::factory()
                    ->count(3)
                    ->has(Lesson::factory()->count(4))
                    ->has(Tag::factory()->count(3))
                    ->set("featured", null)
                    ->set("is_standalone", true),
                fn() => ["position" => fake()->randomFloat(4, 1, 10)]
            )
            ->hasAttached(
                Challenge::factory()
                    ->count(3)
                    ->set("featured", null)
                    ->has(
                        Workshop::factory()
                            ->set("is_standalone", false)
                            ->set("featured", null)
                            ->count(1)
                            ->has(Lesson::factory()->count(4))
                    )
                    ->has(Tag::factory()->count(3))
                    ->set("position", fake()->randomFloat(4, 1, 10)),

                fn() => ["position" => fake()->randomFloat(4, 1, 10)]
            )
            ->create();

        \App\Models\User::factory(3)->create();

        BlogPost::factory(10)
            ->has(Tag::factory()->count(3))
            ->has(Reaction::factory()->count(3))
            ->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $user = new User([
            "name" => "Roberto Cestari",
            "email" => "robertotcestari@gmail.com",
            "password" => bcrypt("19881988"),
        ]);

        $user->save();

        $user2 = new User([
            "name" => "Icaro Harry",
            "email" => "icaropc17@gmail.com",
            "password" => bcrypt("Codante2023"),
        ]);

        $user2->save();

        $this->call(WorkshopUserSeeder::class);
    }
}
