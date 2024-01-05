<?php

namespace App\Http\Controllers\Auth;

use App\Mail\UserRegistered;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GithubLoginController extends AuthenticatedSessionController
{
    public function githubLogin(Request $request)
    {
        $token = $request->validate([
            "github_token" => "required",
        ]);

        try {
            $githubUserData = Socialite::driver("github")->userFromToken(
                $token["github_token"]
            );

            $user =
                User::where("email", $githubUserData->getEmail())->first() ??
                User::where("github_id", $githubUserData->getId())->first();
            $isNewSignup = false;

            if (!$user) {
                $user = User::create([
                    "name" =>
                        $githubUserData->getName() ??
                        $githubUserData->getNickname(),
                    "email" => $githubUserData->getEmail(),
                    "password" => Hash::make(Str::random(10)),
                    "github_id" => $githubUserData->getId(),
                    "github_user" => $githubUserData->getNickname(),
                    "avatar_url" => $githubUserData->getAvatar(),
                    "email_verified_at" => now(),
                ]);

                $isNewSignup = true;
                event(new Registered($user));
                // send UserRegistered email
                Mail::to($user->email)->send(new UserRegistered($user));
            }

            // update avatar and github id (útil caso o user tenha previamente se cadastrado com email e senha)
            $user->avatar_url = $githubUserData->getAvatar();
            $user->github_id = $githubUserData->getId();
            $user->github_user = $githubUserData->getNickname();
            $user->github_data = $githubUserData->user;

            $user->save();

            Auth::login($user);

            $this->deleteUserTokens($request);
            $token = $this->createUserToken($request);

            return response()->json([
                "token" => $token,
                "is_new_signup" => $isNewSignup,
            ]);
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 401);
        }
    }
}
