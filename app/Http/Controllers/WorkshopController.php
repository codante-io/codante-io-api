<?php

namespace App\Http\Controllers;

use App\Http\Resources\WorkshopResource;
use App\Models\Workshop;
use Illuminate\Http\Request;

class WorkshopController extends Controller
{
    public function index()
    {
        return WorkshopResource::collection(
            Workshop::query()
                ->with('lessons')
                ->with('instructor')
                ->with('categories')
                ->visible()
                ->get()
        );
    }

    public function show($slug)
    {
        return new WorkshopResource(
            Workshop::where('slug', $slug)
                ->with('lessons')
                ->with('instructor')
                ->with('categories')
                ->firstOrFail()
        );
    }
}
