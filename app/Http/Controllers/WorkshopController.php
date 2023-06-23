<?php

namespace App\Http\Controllers;

use App\Http\Resources\WorkshopResource;
use App\Models\Tag;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class WorkshopController extends Controller
{
    public function index()
    {
        return WorkshopResource::collection(
            Workshop::query()
                ->where("is_standalone", true)
                ->with("lessons")
                ->with("instructor")
                ->with("tags")
                ->visible()
                ->get()
        );
    }

    public function show($slug)
    {
        // if not logged in, we show cached version
        if (!Auth::guard("sanctum")->check()) {
            return $this->showCachedWorkshop($slug);
        }

        // if logged in show completed lessons
        return $this->showWorkshopWithCompletedLessons($slug);
    }

    private function showCachedWorkshop($slug)
    {
        return Cache::remember(
            "workshop_content_$slug",
            60 * 5,
            function () use ($slug) {
                return new WorkshopResource(
                    Workshop::where("slug", $slug)
                        ->with("lessons")
                        ->with("instructor")
                        ->with("tags")
                        ->firstOrFail()
                );
            }
        );
    }

    private function showWorkshopWithCompletedLessons($slug)
    {
        $workshop = Workshop::where("slug", $slug)
            ->with([
                "lessons",
                "lessons.users" => function ($query) {
                    $query
                        ->select("users.id")
                        ->where("user_id", Auth::guard("sanctum")->id());
                },
            ])
            ->with("instructor")
            ->with("tags")
            ->firstOrFail();

        $workshop->lessons->each(function ($lesson) {
            $lesson->user_completed = $lesson->users->count() > 0;
            unset($lesson->users);
        });

        return new WorkshopResource($workshop);
    }
}
