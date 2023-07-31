<?php

namespace App\Http\Controllers;

use App\Http\Resources\TechnicalAssessmentCardResource;
use App\Http\Resources\TechnicalAssessmentResource;
use App\Models\TechnicalAssessment;
use Illuminate\Http\Request;

class TechnicalAssessmentController extends Controller
{
    public function index()
    {
        return TechnicalAssessmentCardResource::collection(
            TechnicalAssessment::where("status", "published")
                ->with([
                    "tags" => function ($query) {
                        $query->select("name");
                    },
                ])
                ->get()
        );
    }

    public function show($slug)
    {
        return new TechnicalAssessmentResource(
            TechnicalAssessment::where("slug", $slug)
                ->where("status", "published")
                ->with([
                    "tags" => function ($query) {
                        $query->select("name");
                    },
                ])
                ->firstOrFail()
        );
    }
}
