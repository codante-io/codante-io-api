<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChallengeCardResource;
use App\Http\Resources\HomeResource;
use App\Http\Resources\TrackResource;
use App\Http\Resources\WorkshopResource;
use App\Models\Challenge;
use App\Models\Track;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        return Cache::remember("home_content", 60 * 60, function () {
            return [
                "featured_workshops" => WorkshopResource::collection(
                    Workshop::query()
                        ->where("featured", "landing")
                        ->where(function ($query) {
                            $query
                                ->where("status", "published")
                                ->orWhere("status", "soon");
                        })
                        ->with("lessons")
                        ->with("instructor")
                        ->with("tags")
                        ->get()
                ),
                "featured_challenges" => ChallengeCardResource::collection(
                    Challenge::query()
                        ->where("featured", "landing")
                        ->where(function ($query) {
                            $query
                                ->where("status", "published")
                                ->orWhere("status", "soon");
                        })
                        ->with("workshop")
                        ->with("workshop.lessons")
                        ->withCount("users")
                        ->with("users")
                        ->with("tags")
                        ->orderBy("status", "asc")
                        ->orderBy("position", "asc")
                        ->orderBy("published_at", "desc")
                        ->get()
                ),
                "featured_tracks" => TrackResource::collection(
                    Track::query()
                        ->where("featured", "landing")
                        ->where(function ($query) {
                            $query
                                ->where("status", "published")
                                ->orWhere("status", "soon");
                        })
                        ->with("workshops")
                        ->with("challenges")
                        ->with("tags")
                        ->get()
                ),
            ];
        });
    }
}
