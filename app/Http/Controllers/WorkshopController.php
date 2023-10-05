<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\WorkshopRequest;
use App\Http\Resources\LessonResource;
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
                ->orderBy("status")
                ->listed()
                ->get()
        );
    }

    public function show($slug)
    {
        Auth::shouldUse("sanctum");

        // if not logged in, we show cached version
        if (!Auth::check()) {
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
                        ->visible()
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
            ->visible()
            ->firstOrFail();

        $workshop->lessons->each(function ($lesson) {
            $lesson->user_completed = $lesson->users->count() > 0;
            unset($lesson->users);
        });

        return new WorkshopResource($workshop);
    }
}
