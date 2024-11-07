<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
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

        $this->deleteUserTokens($request);
        $token = $this->createUserToken(
            $request,
            $request->input("token_name") ?? "api_token"
        );

        return response()->json([
            "token" => $token,
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        if ($request->user() && $request->user()->tokens) {
            $request
                ->user()
                ->tokens()
                ->where("name", "api_token")
                ->delete();
        }

        return response()->noContent();
    }

    protected function createUserToken($request, $tokenName = "api_token")
    {
        // Create a new token and get only the token
        $fullToken = Auth::user()->createToken($tokenName)->plainTextToken;
        $token = explode("|", $fullToken)[1];

        return $token;
    }

    protected function impersonate(Request $request)
    {
        $user = Auth::user();

        $userId = $request->input("user_id");

        if ($user->is_admin) {
            $userId = $request->input("user_id");
            $userToTokenize = User::find($userId);

            if ($userToTokenize) {
                $fullToken = $userToTokenize->createToken("api_token")
                    ->plainTextToken;
                $token = explode("|", $fullToken)[1];
                return response()->json(["token" => $token]);
            } else {
                return response()->json(
                    ["error" => "Usuário não encontrado"],
                    404
                );
            }
        } else {
            return response()->json(["error" => "Permissão negada"], 403);
        }
    }

    protected function deleteUserTokens($request)
    {
        if ($request->user() && $request->user()->tokens) {
            $request
                ->user()
                ->tokens()
                ->where("name", "api_token")
                ->delete();
        }
    }
}
