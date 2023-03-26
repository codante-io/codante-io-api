<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GithubLoginController extends AuthenticatedSessionController
{
    public function githubLogin(Request $request)
    {
        $token = $request->validate([
            'github_token' => 'required',
        ]);

        try {
            $githubUserData = Socialite::driver('github')->userFromToken($token['github_token']);

            $user = User::where('email', $githubUserData->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $githubUserData->getName(),
                    'email' => $githubUserData->getEmail(),
                    'password' => Hash::make(Str::random(10)),
                    'github_id' => $githubUserData->getId(),
                    'avatar_url' => $githubUserData->getAvatar(),
                    'email_verified_at' => now(),
                ]);
            }

            // update avatar and github id
            $user->avatar_url = $githubUserData->getAvatar();
            $user->github_id = $githubUserData->getId();
            $user->save();

            Auth::login($user);

            $this->deleteUserTokens($request);
            $token = $this->createUserToken($request);

            return response()
                ->json([
                    'token' => $token,
                ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }
}
