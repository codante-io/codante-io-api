<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChallengeCardResource;
use App\Http\Resources\ChallengeUserCardResource;
use App\Http\Resources\PlanResource;
use App\Http\Resources\TestimonialResource;
use App\Http\Resources\UserAvatarResource;
use App\Http\Resources\WorkshopResource;
use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Models\Plan;
use App\Models\Track;
use App\Models\User;
use App\Models\Workshop;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        return Cache::remember("home_content", 60 * 60, function () {
            return [
                "avatar_section" => [
                    "user_count" => User::count(),
                    "avatars" => UserAvatarResource::collection(
                        User::select("avatar_url", "is_pro", "is_admin")
                            ->inRandomOrder()
                            ->take(16)
                            ->get()
                    ),
                ],
                "live_streaming_workshop" => Workshop::where(
                    "status",
                    "streaming"
                )
                    ->select("id", "name", "slug")
                    ->first(),
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
                        ->select(
                            "id",
                            "name",
                            "slug",
                            "short_description",
                            "image_url",
                            "status",
                            "difficulty"
                        )
                        ->where("featured", "landing")
                        ->where(function ($query) {
                            $query
                                ->where("status", "published")
                                ->orWhere("status", "soon");
                        })
                        ->with("workshop:id,challenge_id")
                        ->withCount("users")
                        ->with([
                            "users" => function ($query) {
                                $query
                                    ->select(
                                        "users.id",
                                        "users.name",
                                        "users.is_pro",
                                        "users.avatar_url",
                                        "users.is_admin"
                                    )
                                    ->inRandomOrder()
                                    ->limit(5);
                            }, // nao ordena por usuário logado pois informaçoes estão cacheadas
                        ])
                        ->with("tags")
                        ->orderBy("status", "asc")
                        ->orderBy("position", "asc")
                        ->orderBy("created_at", "desc")
                        ->get()
                ),
                "featured_testimonials" => TestimonialResource::collection(
                    Testimonial::query()
                        ->where("featured", "landing")
                        ->get()
                ),
                "featured_submissions" => ChallengeUserCardResource::collection(
                    ChallengeUser::query()
                        ->where("featured", "landing")
                        ->with([
                            "user" => function ($query) {
                                $query->select(
                                    "users.id",
                                    "users.avatar_url",
                                    "users.name"
                                );
                            },
                        ])
                        ->with([
                            "challenge" => function ($query) {
                                $query->select(
                                    "challenges.id",
                                    "challenges.slug",
                                    "challenges.name"
                                );
                            },
                        ])
                        ->inRandomOrder()
                        ->get()
                ),
                "plan_info" => new PlanResource(Plan::find(1)),
            ];
        });
    }
}
