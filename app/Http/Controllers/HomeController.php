<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChallengeResource;
use App\Http\Resources\HomeResource;
use App\Http\Resources\TrackResource;
use App\Http\Resources\WorkshopResource;
use App\Models\Challenge;
use App\Models\Track;
use App\Models\Workshop;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {

        return [
            'featured_workshops' => WorkshopResource::collection(
                Workshop::query()
                    ->where('featured', 'landing')
                    ->with('lessons')
                    ->with('instructor')
                    ->with('tags')
                    ->get()
            ),
            'featured_challenges' => ChallengeResource::collection(
                Challenge::query()
                    ->where('featured', 'landing')
                    ->where('status', 'published')
                    ->orWhere('status', 'soon')
                    ->with('workshop')
                    ->with('workshop.lessons')
                    ->with('tags')
                    ->get()
            ),
            'featured_tracks' => TrackResource::collection(
                Track::query()
                    ->where('featured', 'landing')
                    ->with('workshops')
                    ->with('challenges')
                    ->with('tags')
                    ->get()
            )
        ];
    }
}
