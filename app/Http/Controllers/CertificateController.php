<?php

namespace App\Http\Controllers;

use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Notifications\Discord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    public function show($slug)
    {
        Auth::shouldUse("sanctum");

        $challengeUser = ChallengeUser::query()
            ->where("user_id", auth()->id())
            ->where(
                "challenge_id",
                Challenge::where("slug", $slug)->first()->id
            )
            ->with("challenge")
            ->firstOrFail();

        $certificate = $challengeUser
            ->certificate()
            ->with("user")
            ->with("certifiable")
            ->firstOrFail();

        return new CertificateResource($certificate);
    }

    public function showById($id)
    {
        $certificate = Certificate::query()
            ->where("id", $id)
            ->with("user")
            ->with("certifiable")
            ->firstOrFail();
        return new CertificateResource($certificate);
    }

    public function create(Request $request)
    {
        Auth::shouldUse("sanctum");

        $request->validate([
            "certifiable_type" => "required|in:ChallengeUser",
            "certifiable_id" => "required|string",
        ]);

        $user = Auth::user();

        $exists = Certificate::where([
            "certifiable_type" => Certificate::validateCertifiable(
                $request->certifiable_type
            ),
            "certifiable_id" => $request->certifiable_id,
            "user_id" => $user->id,
        ])->exists();

        if ($exists) {
            throw new \Exception("Já existe um certificado.");
        }

        $certificate = new Certificate();
        $certificate->user_id = $user->id;
        $certifiableClass = $certificate->certifiable_type = Certificate::validateCertifiable(
            $request->certifiable_type
        );
        $certificate->certifiable_id = $request->certifiable_id;
        $certificate->status = "pending";

        $certifiable = $certifiableClass::findOrFail($request->certifiable_id);

        if ($request->certifiable_type === "ChallengeUser") {
            $challengeUser = ChallengeUser::with("challenge")->findOrFail(
                $request->certifiable_id
            );
            $challenge = $challengeUser->challenge;
            $certificate->metadata = [
                "certifiable_source_name" => $challenge->name,
                "end_date" =>
                    $challengeUser->submitted_at ??
                    now()->format("Y-m-d H:i:s"),
                "start_date" =>
                    $challengeUser->created_at ?? now()->format("Y-m-d H:i:s"),
                "tags" => $challenge->tags->pluck("name"),
                "certifiable_slug" => $challenge->slug,
            ];
        }

        $certificate->save();

        event(
            new \App\Events\UserRequestedCertificate(
                $user,
                $certificate,
                $certifiable
            )
        );

        return $certificate;
    }
}
