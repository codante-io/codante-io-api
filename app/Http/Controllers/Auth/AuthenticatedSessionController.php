<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        // dd($request->user()->tokens->first());
        $request->session()->regenerate();

        auth()->user()->tokens()->delete();

        $fullToken = $request->user()->createToken('api_token')->plainTextToken;
        $token = explode('|', $fullToken)[1];

        return response()
            ->json([
                'token' => $token,
            ])->withCookie(cookie('user_token', $token, 60 * 24 * 30));
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
}
