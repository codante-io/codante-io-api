<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
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
            "metadata" => "optional|array",
            "status" => "optional|string",
        ]);

        $certificateData = [
            'user_id' => $request->user_id,
            'source_type' => $request->source_type,
            'metadata' => json_encode($request->metadata),
            'status' => $request->status ?? 'pending',
        ];

        if ($request->source_type === 'challenge') {
            $certificateData['challenge_id'] = $request->source_id;
        } elseif ($request->source_type === 'workshop') {
            $certificateData['workshop_id'] = $request->source_id;
        }

        $certificate = Certificate::create($certificateData);

        return $certificate;
    }
}
