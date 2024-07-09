<?php

namespace App\Http\Controllers;

use App\Events\UserEnteredWorkshop;
use App\Events\UsersFirstWorkshop;
use App\Http\Resources\LessonResource;
use App\Http\Resources\WorkshopCardResource;
use App\Http\Resources\WorkshopResource;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class WorkshopController extends Controller
{
    public function index()
    {
        return WorkshopCardResource::collection(
            Workshop::query()
                ->withCount("lessons")
                ->withSum("lessons", "duration_in_seconds")
                ->with("instructor")
                ->orderByRaw(
                    "CASE WHEN status = 'streaming' THEN 1 WHEN status = 'published' THEN 2 WHEN status = 'soon' THEN 3 ELSE 4 END"
                )
                ->orderBy("is_standalone", "desc")
                ->orderBy("published_at", "desc")
                ->listed()
                ->get()
        );
    }

    public function show($slug)
    {
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
            ->with("challenge")
            ->visible()
            ->firstOrFail();

        $workshop->lessons->each(function ($lesson) {
            $lesson->user_completed = $lesson->users->count() > 0;
            unset($lesson->users);
        });

        $workshop->next_lesson = new LessonResource(
            $workshop->lessons->first(function ($lesson) {
                return !$lesson->user_completed;
            })
        );

        return new WorkshopResource($workshop);
    }

    public function userEnteredWorkshop(Request $request, $slug)
    {
        if (!Auth::check()) {
            return response()->json(["message" => "Unauthorized"], 401);
        }

        $workshop = Workshop::where("slug", $slug)->firstOrFail();
        $user = User::find($request->user()->id);

        $isFirstWorkshop = $user->workshops()->count() === 0;

        $user->workshops()->syncWithoutDetaching($workshop->id);

        if ($isFirstWorkshop) {
            event(new UsersFirstWorkshop($user, $workshop));
            return response()->json([
                "message" => "User entered first workshop",
            ]);
        }

        return response()->json(["message" => "User entered workshop"]);
    }
}
