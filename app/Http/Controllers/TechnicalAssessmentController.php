<?php

namespace App\Http\Controllers;

use App\Models\TechnicalAssessment;
use Illuminate\Http\Request;

class TechnicalAssessmentController extends Controller
{
    public function index()
    {
        return TechnicalAssessment::where('status', 'published')->get();
    }
}
