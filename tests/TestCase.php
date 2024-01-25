<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function signInAndReturnToken($user = null)
    {
        $user =
            $user ?:
            \App\Models\User::factory()->create([
                "password" => bcrypt("password"),
            ]);
        // $this->actingAs($user);

        // get api key
        $response = $this->postJson("/api/login", [
            "email" => $user->email,
            "password" => "password",
        ]);

        $token = $response->json()["token"];

        return $token;
    }

    public function signInAndReturnUserAndToken($user = null)
    {
        $user =
            $user ?:
            \App\Models\User::factory()->create([
                "password" => bcrypt("password"),
            ]);

        $response = $this->postJson("/api/login", [
            "email" => $user->email,
            "password" => "password",
        ]);

        $token = $response->json()["token"];

        return ["user" => $user, "token" => $token];
    }
}
