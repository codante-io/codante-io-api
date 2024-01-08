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
    // public function index()
    // {
    //     Auth::shouldUse("sanctum");
    //     return CertificateResource::collection(
    //         Certificate::query()
    //             ->where("user_id", auth()->id())
    //             ->get()
    //     );
    // }

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

        // dd($exists);

        if ($exists) {
            throw new \Exception("Já existe um certificado.");
        }

        $certificate = new Certificate();
        $certificate->user_id = $user->id;
        $certificate->certifiable_type = Certificate::validateCertifiable(
            $request->certifiable_type
        );
        $certificate->certifiable_id = $request->certifiable_id;
        $certificate->status = "pending";

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
            ];
        }

        $certificate->save();

        if ($request->certifiable_type === "ChallengeUser") {
            new Discord(
                "💻 {$challenge->name}\n👤 {$user->name}\n🔗 Submissão: <https://codante.io/mini-projetos/{$challenge->slug}/submissoes/{$user->github_user}>\nPara aprovar, substitua o status para published: <https://api.codante.io/admin/certificate/{$certificate->id}/edit>",
                "pedidos-certificados"
            );
        }

        // if ($source_type === "workshop") {
        //     new Discord(
        //         "💻 Workshop: {$source->name}\n👤 Certificado de Workshop gerado para {$user->name}",
        //         "pedidos-certificados",
        //     );
        // }

        return $certificate;
    }
}
