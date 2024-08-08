<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class SaveAvatarsFromGithub
{
    public static function handle()
    {
        // pega todos os usuários que têm avatar
        $users = User::whereNotNull("avatar_url")->get();

        // // para cada usuário, pega o avatar do github e salva no banco
        foreach ($users as $user) {
            // a url deve começar com https://avatars.githubusercontent.com
            if (
                strpos(
                    $user->avatar_url,
                    "https://avatars.githubusercontent.com"
                ) === false
            ) {
                continue;
            }

            if ($user->avatar_url === null) {
                continue;
            }

            $res = Http::withHeaders([
                "Authorization" =>
                    "Bearer " . config("services.screenshot.token"),
            ])->post(
                config("services.screenshot.base_url") . "/upload-avatar-image",
                [
                    "avatar_url" => $user->avatar_url,
                    "email" => $user->email,
                ]
            );

            if ($res->status() !== 200) {
                continue;
            }

            $user->avatar_url = $res->json()["smImageUrl"];
            $user->save();
        }
    }
}
