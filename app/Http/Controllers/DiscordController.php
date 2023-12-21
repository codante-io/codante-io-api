<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Http;
use Illuminate\Http\Request;
use Laravel\Prompts\Output\ConsoleOutput;

class DiscordController extends Controller
{
    public function handleDiscordLoginButton(Request $request)
    {
        $discordUserData = $this->getUserDataFromDiscordToken($request);

        $user = $request->user();
        $user->discord_user = $discordUserData["username"];
        $user->discord_data = $discordUserData;
        $user->save();

        $this->addDiscordUserToGuild($request, $discordUserData);

        return new UserResource($user);
    }

    protected function getUserDataFromDiscordToken(Request $request)
    {
        $request->validate([
            "access_token" => "required|string|max:255",
        ]);

        $discordUserData = Http::withHeaders([
            "Authorization" => "Bearer " . $request->access_token,
        ])
            ->get("https://discord.com/api/users/@me")
            ->json();

        return $discordUserData;
    }

    protected function addDiscordUserToGuild(Request $request, $discordUserData)
    {
        $request->validate([
            "access_token" => "required|string|max:255",
        ]);

        $response = Http::withHeaders([
            "Content-Type" => "application/json",
            "Authorization" => "Bot " . config("services.discord.bot_token"),
        ])->put(
            "https://discord.com/api/guilds/1089524234142888048/members/" .
                $discordUserData["id"],
            ["access_token" => $request->access_token]
        );

        if ($response->status() >= 400) {
            throw new \Exception("Failed to add user to guild");
        }

        return $response;
    }
}
