<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:sanctum");
    }

    public function changeUserName(Request $request)
    {
        $request->validate([
            "name" => "required|string|max:255",
        ]);

        $user = $request->user();
        $user->name = $request->name;
        $user->save();

        return $user;
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            "password" => "required|confirmed|min:8",
        ]);
        $user = $request->user();
        $user->password = bcrypt($request->password);
        $user->save();

        return $user;
    }

    public function updateSettings(Request $request)
    {
        // Check if showBadge setting is set

        $request->validate([
            "show_badge" => "required|boolean",
        ]);

        $user = $request->user();
        $settings = $user->settings; // Get the settings
        $settings["show_badge"] = $request->show_badge; // Modify the settings
        $user->settings = $settings; // Set the settings back on the model
        $user->save();

        return new UserResource($user);
    }

    public function updateUser(UpdateUserRequest $request, User $user)
    {
        dd($user);
        $user->update($request->validated());

        return new UserResource($user);
    }
}
