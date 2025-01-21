<?php

namespace App\Http\Controllers;

use App\Http\Resources\TechnicalAssessmentCardResource;
use App\Http\Resources\TechnicalAssessmentResource;
use App\Models\TechnicalAssessment;

class TechnicalAssessmentController extends Controller
{
    public function index()
    {
        return TechnicalAssessmentCardResource::collection(
            TechnicalAssessment::where(function ($query) {
                $query
                    ->where('status', 'published')
                    ->orWhere('status', 'outdated');
            })
                ->with([
                    'tags' => function ($query) {
                        $query->select('name');
                    },
                ])
                ->orderBy('status', 'desc')
                ->get()
        );
    }

    public function show($slug)
    {
        return new TechnicalAssessmentResource(
            TechnicalAssessment::where('slug', $slug)
                ->where(function ($query) {
                    $query
                        ->where('status', 'published')
                        ->orWhere('status', 'outdated');
                })
                ->with([
                    'tags' => function ($query) {
                        $query->select('name');
                    },
                ])
                ->firstOrFail()
        );
    }
}
