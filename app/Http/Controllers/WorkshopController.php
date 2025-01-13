<?php

namespace App\Http\Controllers;

use App\Events\UserJoinedWorkshop as UserJoinedWorkshopEvent;
use App\Events\UsersFirstWorkshop;
use App\Http\Resources\WorkshopCardResource;
use App\Http\Resources\WorkshopResource;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class WorkshopController extends Controller
{
    public function index(Request $request)
    {
        $query = Workshop::query();
        $query = $this->workshopQueryWithFilters($request, $query);
        $filterName = $request->input('tecnologia');

        $workshops = Cache::remember(
            "workshops-tech-$filterName",
            7200,
            function () use ($query) {
                return $query
                    ->cardQuery()
                    ->orderByRaw(
                        "CASE WHEN status = 'streaming' THEN 1 WHEN status = 'published' THEN 2 WHEN status = 'soon' THEN 3 ELSE 4 END"
                    )
                    ->orderBy('is_standalone', 'desc')
                    ->orderBy('published_at', 'desc')
                    ->listed()
                    ->get();
            }
        );

        return WorkshopCardResource::collection($workshops);
    }

    public function show($slug)
    {
        // if not logged in, we show cached version
        if (! Auth::check()) {
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
                    Workshop::where('slug', $slug)
                        ->with('lessonsSidebarList')
                        ->with('instructor')
                        ->with('tags')
                        ->withSum('lessons', 'duration_in_seconds')
                        ->visible()
                        ->firstOrFail()
                );
            }
        );
    }

    private function showWorkshopWithCompletedLessons($slug)
    {
        $workshop = Workshop::where('slug', $slug)
            ->with('lessonsSidebarListWithUserProgress')
            ->withSum('lessons', 'duration_in_seconds')
            ->with('instructor')
            ->with('tags')
            ->with('challenge')
            ->visible()
            ->firstOrFail();

        return new WorkshopResource($workshop);
    }

    public function userJoined(Request $request, $slug)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $workshop = Workshop::where('slug', $slug)->firstOrFail();
        $userWorkshops = $user->workshops;

        $isFirstWorkshop = $userWorkshops->count() === 0;

        // checks if user is joining the workshop for the first time
        if (! $user->workshops->contains($workshop)) {
            event(new UserJoinedWorkshopEvent($user, $workshop));
        }

        $user->workshops()->syncWithoutDetaching($workshop->id);

        if ($isFirstWorkshop) {
            event(new UsersFirstWorkshop($user, $workshop));

            return response()->json([
                'message' => 'User entered first workshop',
            ]);
        }

        return response()->json(['message' => 'User entered workshop']);
    }

    protected function workshopQueryWithFilters(
        Request $request,
        Builder $query
    ) {
        $query->with('tags:id,name')->with('mainTechnology:id,name');

        // Filtro de Tecnologia (tags)
        if ($request->has('tecnologia')) {
            $technologies = explode(',', $request->input('tecnologia'));

            $query
                ->whereHas('mainTechnology', function ($subquery) use (
                    $technologies
                ) {
                    $subquery->whereIn('slug', $technologies);
                })
                ->orWhere(function ($query) {
                    $query
                        ->where('status', 'soon')
                        ->where('published_at', '>', now());
                });
        }

        // Filtro de Tipo
        if ($request->has('tipo')) {
            $isStandalone = $request->input('tipo') === 'workshop' ? 1 : 0;
            $query->where('is_standalone', $isStandalone);
        }

        // Busca por Texto
        if ($request->has('busca')) {
            $busca = $request->input('busca');
            $query->where(function ($subquery) use ($busca) {
                $subquery
                    ->where('name', 'like', "%$busca%")
                    ->orWhere('short_description', 'like', "%$busca%");
            });
        }

        return $query;
    }
}
