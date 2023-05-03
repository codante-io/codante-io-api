<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function changeUserName(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $user->name = $request->name;
        $user->save();

        return $user;
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:8',
        ]);
        $user = $request->user();
        $user->password = bcrypt($request->password);
        $user->save();

        return $user;
    }
}
