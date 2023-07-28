<?php

namespace App\Http\Controllers;

use App\Http\Resources\TechnicalAssessmentResource;
use App\Models\TechnicalAssessment;
use Illuminate\Http\Request;

class TechnicalAssessmentController extends Controller
{
    public function index()
    {
        return TechnicalAssessmentResource::collection(
            TechnicalAssessment::where("status", "published")
                ->with([
                    "tags" => function ($query) {
                        $query->select("name");
                    },
                ])
                ->get()
        );
    }
}
