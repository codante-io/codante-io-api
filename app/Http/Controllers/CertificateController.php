<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Models\User;
use App\Models\Workshop;
use App\Notifications\Discord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    public function index()
    {
    }

    public function createRequestForCertificate(Request $request)
    {
        Auth::shouldUse("sanctum");

        $request->validate([
            "user_id" => "required|string",
            "source_type" => "required|in:workshop,challenge",
            "source_id" => "required|string",
            "status" => "optional|string",
        ]);

        $certificateData = [
            'user_id' => $request->user_id,
            'source_type' => $request->source_type,
            'status' => $request->status ?? 'pending',
        ];

        $user = User::find($request->user_id);
        if ($request->source_type === 'challenge') {
            $certificateData['challenge_id'] = $request->source_id;

            $challenge = Challenge::find($request->source_id);
            $challenge_user = ChallengeUser::where('challenge_id', $challenge->id)->where('user_id', $user->id)->first();
            $certificateData['metadata'] = [
                [
                    "duration" => null,
                    "source_name" => $challenge->name,
                    "conclusion_date" => $challenge_user->submitted_at ?? now()->format('Y-m-d H:i:s')
                    // "tags" => $challenge->tags,
                ]
            ];
        } elseif ($request->source_type === 'workshop') {
            $certificateData['workshop_id'] = $request->source_id;

            $workshop = Workshop::find($request->source_id);
            $certificateData['metadata'] = [
                [
                    "duration" => $workshop->duration_in_minutes,
                    "source_name" => $workshop->name,
                    "conclusion_date" => now(),
                ]
            ];
        }

        $certificate = Certificate::create($certificateData);

        if ($request->source_type === 'challenge') {
            new Discord(
                "💻 {$challenge->name}\n👤 {$user->name}\n🔗 Submissão: <https://codante.io/mini-projetos/{$challenge->slug}/submissoes/{$user->github_user}>\nPara aprovar, substitua o status para published: <https://api.codante.io/admin/certificate/{$certificate->id}/edit>",
                "pedidos-certificados",
            );
        }

        if ($request->source_type === "workshop") {
            $workshop = Workshop::find($request->source_id);
            new Discord(
                "💻 Workshop: {$workshop->name}\n👤 Certificado de Workshop gerado para {$user->name}",
                "pedidos-certificados",
            );
        }

        return $certificate;
    }
}
