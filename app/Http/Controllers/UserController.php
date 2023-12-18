<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Http;
use Illuminate\Http\Request;
use Socialite;

class UserController extends Controller
{
    public function update(UpdateUserRequest $request)
    {
        // $request->validate([
        //     "email" => "required|email|unique:users,email," . $this->user()->id,
        //     "name" => "sometimes|string|max:255",
        // ]);

        $data = $request->validated();

        // update only the validated data
        $user = $request->user();
        $user->update($data);

        return $user;
    }

    public function updateDiscord(Request $request)
    {
        $request->validate([
            "access_token" => "required|string|max:255",
            "token_type" => "required|string|max:255",
            "expires_in" => "required|integer",
            "refresh_token" => "required|string|max:255",
            "scope" => "required|string|max:255",
        ]);

        $discordUserData = Http::withHeaders([
            "Authorization" => "Bearer " . $request->access_token,
        ])
            ->get("https://discord.com/api/users/@me")
            ->json();

        $user = $request->user();
        $user->discord_user = $discordUserData["username"];
        $user->discord_data = $discordUserData;
        $user->save();

        return new UserResource($user);
    }

    public function addDiscordUserToGuild()
    {
    }
}
