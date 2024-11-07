<?php

namespace App\Http\Controllers\AdminApi;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use Illuminate\Http\Request;

class WorkshopAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(["auth:sanctum", "is-admin"]);
    }

    public function editWorkshop(Request $request, $id)
    {
        // adicionar aqui o que seria possível alterar.
        $validated = $request->validate([
            "video_url" => "string",
        ]);

        if (!$validated) {
            return response()->json(["message" => "Dados inválidos."], 400);
        }

        $workshop = Workshop::find($id);
        if (!$workshop) {
            return response()->json(["message" => "Workshop not found"], 404);
        }

        $workshop->update($validated);

        return response()->json(
            [
                "message" => "Workshop updated successfully",
                "workshop" => $workshop,
            ],
            200
        );
    }
}
