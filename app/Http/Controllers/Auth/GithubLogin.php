<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GithubLogin extends AuthenticatedSessionController
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $this->deleteUserTokens($request);
        $token = $this->createUserToken($request);

        return response()
            ->json([
                'token' => $token,
            ])->withCookie(cookie('user_token', $token, 60 * 24 * 30));
    }


    public function githubLogin(Request $request): JsonResponse
    {
        $token = $request->validate([
            'github_token' => 'required',
        ]);

        $githubUser = Socialite::driver('github')->userFromToken($token['github_token']);

        // dd($user);
        // Auth::login($user);


        // $request->session()->regenerate();

        // $this->deleteUserTokens($request);
        // $token = $this->createUserToken($request);


        // return response()
        //     ->json([
        //         'token' => $token
        //     ]);
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        if ($request->user() && $request->user()->tokens) {
            $request->user()->tokens()->delete();
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }

    protected function createUserToken($request)
    {
        // Create a new token and get only the token
        $fullToken = auth()->user()->createToken('api_token')->plainTextToken;
        $token = explode('|', $fullToken)[1];

        return $token;
    }

    protected function deleteUserTokens($request)
    {
        if ($request->user() && $request->user()->tokens) {
            $request->user()->tokens()->delete();
        }
    }
}
