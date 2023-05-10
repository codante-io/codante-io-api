<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class CalendarController extends Controller
{

    public function showCalendar()
    {

        $workshopColumns = Schema::getColumnListing('workshops');
        $workshopColumns = array_diff($workshopColumns, ['description', 'video_url', 'created_at', 'updated_at', 'deleted_at']);

        $challengeColumns = Schema::getColumnListing('challenges');
        $challengeColumns = array_diff($challengeColumns, ['description', 'video_url', 'created_at', 'updated_at', 'deleted_at']);


        // pegar os próximos workshops (soon + data)
        $workshops = Workshop::query()
            ->where('status', 'soon')
            ->where('is_standalone', true)
            ->with('instructor', fn ($query) => $query->select('id', 'name', 'company', 'avatar_url'))
            ->with('tags', fn ($query) => $query->select('name'))
            ->whereDate('published_at', '>=', now())
            ->select($workshopColumns)
            ->get();


        $challenges = Challenge::query()
            ->where('status', '!=', 'draft')
            ->with('tags', fn ($query) => $query->select('name'))
            ->whereDate('published_at', '>=', now())
            ->select($challengeColumns)
            ->get();



        $challenges->append('type');
        $workshops->append('type');

        $merged = $workshops->merge($challenges)->sortBy('published_at');

        $result = [];

        foreach ($merged as $item) {

            $date = $item->published_at->format('Y-m-d');
            $result[$date][] = $item;
        }

        return $result;
    }
}
