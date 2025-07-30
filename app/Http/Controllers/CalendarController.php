<?php

namespace App\Http\Controllers;

use App\Http\Resources\Calendar\NewChallengeEventResource;
use App\Http\Resources\Calendar\NewChallengeResolutionEventResource;
use App\Http\Resources\Calendar\NewWorkshopEventResource;
use App\Models\Challenge;
use App\Models\Workshop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CalendarController extends Controller
{
    public function getEventsByDate(Request $request)
    {
        if (! $request->input('start_date')) {
            $startDate = Carbon::parse('2022-01-01')->toIso8601String();
        } else {
            $startDate = Carbon::parse($request->input('start_date'))->toIso8601String();
        }

        if (! $request->input('end_date')) {
            $endDate = Carbon::parse('2122-12-31')->toIso8601String();
        } else {
            $endDate = Carbon::parse($request->input('end_date'))->toIso8601String();
        }

        $cacheKey = 'events_'.$startDate.'_'.$endDate;

        $events = Cache::remember($cacheKey, 3600 * 4, function () use ($request, $startDate, $endDate) {
            $challenges = Challenge::query()
                ->listed()
                ->where('weekly_featured_start_date', '>=', $startDate)
                ->where('weekly_featured_start_date', '<=', $endDate)
                ->get();

            $workshops = Workshop::query()
                ->listed()
                ->where('is_standalone', true)
                ->where('published_at', '>=', $startDate)
                ->where('published_at', '<=', $endDate)
                ->get();

            $challengeResolutions = Challenge::query()
                ->listed()
                ->where('solution_publish_date', '>=', $startDate)
                ->where('solution_publish_date', '<=', $endDate)
                ->get();

            $events = collect([
                ...NewChallengeEventResource::collection($challenges)->toArray($request),
                ...NewWorkshopEventResource::collection($workshops)->toArray($request),
                ...NewChallengeResolutionEventResource::collection($challengeResolutions)->toArray($request),
            ]);

            return $events->sortByDesc('datetime')->values();
        });

        return $events;
    }
}
