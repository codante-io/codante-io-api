<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Http\Resources\WorkshopCardResource;
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

    public function changeLinkedinUser(Request $request)
    {
        $request->validate([
            'linkedin_user' => 'required|string',
        ]);

        $user = $request->user();
        $user->linkedin_user = $request->linkedin_user;
        $user->save();

        return $user;
    }

    public function updateSettings(Request $request)
    {
        // Check if showBadge setting is set

        $request->validate([
            'show_badge' => 'required|boolean',
        ]);

        $user = $request->user();
        $settings = $user->settings; // Get the settings
        $settings['show_badge'] = $request->show_badge; // Modify the settings
        $user->settings = $settings; // Set the settings back on the model
        $user->save();

        return new UserResource($user);
    }

    public function getDashboardData(Request $request)
    {
        $user = $request->user();

        $challengeUsers = $user->challengeUsers
            ->sortByDesc('updated_at')
            ->map(function ($challengeUser) {
                return [
                    'id' => $challengeUser->id,
                    'challenge_id' => $challengeUser->challenge_id,
                    'completed' => $challengeUser->completed,
                    'challenge_name' => $challengeUser->challenge->name,
                    'challenge_image' => $challengeUser->challenge->image_url,
                    'challenge_slug' => $challengeUser->challenge->slug,
                    'listed' => $challengeUser->listed,
                    'submission_url' => $challengeUser->submission_url,
                    'submission_image_url' => $challengeUser->submission_image_url,
                ];
            })
            ->values();

        $certificates = $user->certificates->map(function ($certificate) {
            $certifiable = $certificate->certifiable;
            $certifiableName =
                $certificate->certifiable_type === "App\Models\ChallengeUser"
                    ? $certifiable->challenge->name
                    : $certifiable->workshop->name;

            return [
                'id' => $certificate->id,
                'certifiable_type' => class_basename(
                    $certificate->certifiable_type
                ),
                'status' => $certificate->status,
                'certifiable_name' => $certifiableName,
            ];
        });

        return response()->json([
            'challenge_users' => $challengeUsers,
            'certificates' => $certificates,
        ]);
    }

    public function getWorkshops(Request $request)
    {
        $user = $request->user();

        $workshops = $user
            ->workshops()
            ->cardQuery()
            ->get();
        $workshopCard = WorkshopCardResource::collection($workshops);

        return response()->json($workshopCard);
    }

    public function changeAvatar(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'avatar' => 'required|image',
        ]);

        $user->changeAvatar($request->file('avatar'));

        return [
            'success' => true,
            'message' => 'Avatar changed successfully',
        ];
    }
}
