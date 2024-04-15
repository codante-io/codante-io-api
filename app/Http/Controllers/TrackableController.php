<?php

namespace App\Http\Controllers;

use App\Models\Trackable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackableController extends Controller
{
    public function markAsCompleted($trackableId, Request $request)
    {
        Auth::shouldUse("sanctum");

        $user = Auth::user();

        if (!$user->is_pro) {
            return response()->json(
                [
                    "ok" => false,
                    "message" =>
                        "Você precisa ser um membro PRO para marcar como concluído.",
                ],
                403
            );
        }

        $trackableExists = Trackable::query()->find($trackableId);

        if (!$trackableExists) {
            return response()->json(
                ["ok" => false, "message" => "Trackable não encontrado."],
                404
            );
        }

        $trackable = $user
            ->trackables()
            ->where("trackable_user.trackable_id", $trackableId)
            ->withPivot(["completed"])
            ->first();

        if ($trackable) {
            $currentCompletedStatus = $trackable->pivot->completed;
            $user->trackables()->updateExistingPivot($trackableId, [
                "completed" => !$currentCompletedStatus,
            ]);

            return response()->json(
                ["ok" => true, "completed" => !$currentCompletedStatus],
                200
            );
        }

        $user->trackables()->attach($trackableId, [
            "completed" => true,
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        return response()->json(["ok" => true, "completed" => true], 200);
    }
}
