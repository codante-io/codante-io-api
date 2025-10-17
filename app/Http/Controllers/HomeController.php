<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChallengeCardResource;
use App\Http\Resources\ChallengeUserCardResource;
use App\Http\Resources\PlanResource;
use App\Http\Resources\TestimonialResource;
use App\Http\Resources\UserAvatarResource;
use App\Http\Resources\WorkshopCardResource;
use App\Models\BlogPost;
use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Models\Plan;
use App\Models\TechnicalAssessment;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Support\Facades\Cache;
use Response;

class HomeController extends Controller
{
    public function index()
    {
        return Cache::remember('home_content', 60 * 60, function () {
            return [
                'avatar_section' => [
                    'user_count' => User::count(),
                    'avatars' => UserAvatarResource::collection(
                        User::select('avatar_url', 'is_pro', 'is_admin')
                            ->inRandomOrder()
                            ->take(16)
                            ->get()
                    ),
                ],
                'live_streaming_workshop' => Workshop::where(
                    'status',
                    'streaming'
                )
                    ->select('id', 'name', 'slug')
                    ->first(),
                'featured_workshops' => WorkshopCardResource::collection(
                    Workshop::query()
                        ->withCount('lessons')
                        ->withSum('lessons', 'duration_in_seconds')
                        ->with('instructor')
                        ->where('featured', 'landing')
                        ->orderByRaw(
                            "CASE WHEN status = 'streaming' THEN 1 WHEN status = 'published' THEN 2 WHEN status = 'soon' THEN 3 ELSE 4 END"
                        )
                        ->orderBy('published_at', 'desc')
                        ->listed()
                        ->get()
                ),
                'featured_challenges' => ChallengeCardResource::collection(
                    Challenge::query()
                        ->select(
                            'id',
                            'name',
                            'slug',
                            'short_description',
                            'image_url',
                            'status',
                            'difficulty',
                            'estimated_effort',
                            'main_technology_id',
                            'category',
                            'is_premium'
                        )
                        ->where('featured', 'landing')
                        ->where(function ($query) {
                            $query
                                ->where('status', 'published')
                                ->orWhere('status', 'soon');
                        })
                        ->with('workshop:id,challenge_id')
                        ->withCount('users')
                        ->with('mainTechnology')
                        ->with([
                            'users' => function ($query) {
                                $query
                                    ->select(
                                        'users.id',
                                        'users.name',
                                        'users.is_pro',
                                        'users.avatar_url',
                                        'users.is_admin'
                                    )
                                    ->whereNotNull('avatar_url')
                                    ->orderBy('challenge_user.created_at', 'desc')
                                    ->limit(5);
                            }, // nao ordena por usuário logado pois informaçoes estão cacheadas
                        ])
                        ->with('tags')
                        ->orderBy('status', 'asc')
                        ->orderBy('position', 'asc')
                        ->orderBy('created_at', 'desc')
                        ->get()
                ),
                'featured_testimonials' => TestimonialResource::collection(
                    Testimonial::query()
                        ->where('featured', 'landing')
                        ->orderBy('position', 'asc')
                        ->get()
                ),
                'featured_submissions' => ChallengeUserCardResource::collection(
                    ChallengeUser::query()
                        ->where('featured', 'landing')
                        ->with([
                            'user' => function ($query) {
                                $query->select(
                                    'users.id',
                                    'users.avatar_url',
                                    'users.name',
                                    'users.github_user'
                                );
                            },
                        ])
                        ->with([
                            'challenge' => function ($query) {
                                $query->select(
                                    'challenges.id',
                                    'challenges.slug',
                                    'challenges.name'
                                );
                            },
                        ])
                        ->inRandomOrder()
                        ->get()
                ),
                'submission_count' => ChallengeUser::where('submission_url', '!=', null)->count(),
                'plan_info' => new PlanResource(Plan::find(1)),
            ];
        });
    }

    public function sitemap()
    {
        $itemsArray = Cache::remember('sitemap', 60 * 60, function () {
            return $this->getSitemapItems();
        });

        return Response::json($itemsArray)->header(
            'Cache-Control',
            'public, max-age=3600'
        );
    }

    protected function getSitemapItems()
    {
        $workshops = Workshop::listed()
            ->select('id', 'slug', 'updated_at', 'challenge_id')
            ->withCount('lessons')
            ->with([
                'lessons' => function ($query) {
                    $query->select('workshop_id', 'slug', 'updated_at');
                },
            ])
            ->get();

        $challenges = Challenge::where('status', 'published')
            ->select('id', 'slug', 'updated_at')
            ->get();

        $blogPosts = BlogPost::where('status', 'published')
            ->where('type', 'blog')
            ->select('slug', 'updated_at')
            ->get();

        $technicalAssessments = TechnicalAssessment::where(
            'status',
            'published'
        )
            ->select('id', 'slug', 'updated_at')
            ->get();

        $workshopArray = $workshops->map(function ($workshop) {
            return [
                'url' => config('app.frontend_url').
                    '/'.
                    'workshops'.
                    '/'.
                    $workshop->slug,
                'lastmod' => $workshop->updated_at->format('Y-m-d'),
            ];
        });

        $challengeArray = $challenges->map(function ($challenge) {
            return [
                'url' => config('app.frontend_url').
                    '/mini-projetos/'.
                    $challenge->slug,
                'lastmod' => $challenge->updated_at->format('Y-m-d'),
            ];
        });

        $blogPostArray = $blogPosts->map(function ($blogPost) {
            return [
                'url' => config('app.frontend_url').'/blog/'.$blogPost->slug,
                'lastmod' => $blogPost->updated_at->format('Y-m-d'),
            ];
        });

        $submissionArray = [];
        // get all submissions from challenges
        foreach ($challenges as $challenge) {
            $submissions = ChallengeUser::where('challenge_id', $challenge->id)
                ->where('completed', '1')
                ->where('is_solution', '0')
                ->with([
                    'user' => function ($query) {
                        $query->select('id', 'github_user', 'updated_at');
                    },
                ])
                ->select('user_id', 'id', 'updated_at')
                ->get();

            $submissionArray = array_merge(
                $submissionArray,
                $submissions
                    ->map(function ($submission) use ($challenge) {
                        return [
                            'url' => config('app.frontend_url').
                                '/mini-projetos/'.
                                $challenge->slug.
                                '/submissoes/'.
                                $submission->user->github_user,
                        ];
                    })
                    ->toArray()
            );
        }

        $workshopLessonsArray = [];
        $challengesWithTutorialArray = [];

        foreach ($workshops as $workshop) {
            // Lessons from workshop
            $workshopLessonsArray = array_merge(
                $workshopLessonsArray,
                $workshop->lessons
                    ->map(function ($lesson) use ($workshop) {
                        return [
                            'url' => config('app.frontend_url').
                                '/workshops'.
                                '/'.
                                $workshop->slug.
                                '/'.
                                $lesson->slug,
                            'lastmod' => $lesson->updated_at->format('Y-m-d'),
                        ];
                    })
                    ->toArray()
            );

            // workshop that belongs to challenge, we will have the /tutorial and /codigo pages
            if ($workshop->challenge_id) {
                $challenge = $challenges
                    ->where('id', $workshop->challenge_id)
                    ->first();

                if ($workshop->lessons_count > 0) {
                    $challengesWithTutorialArray[] = [
                        'url' => config('app.frontend_url').
                            '/mini-projetos/'.
                            $challenge->slug.
                            '/tutorial',
                    ];

                    $challengesWithTutorialArray[] = [
                        'url' => config('app.frontend_url').
                            '/mini-projetos/'.
                            $challenge->slug.
                            '/codigo',
                    ];
                }
            }
        }

        $technicalAssessmentsArray = $technicalAssessments->map(function (
            $technicalAssessment
        ) {
            return [
                'url' => config('app.frontend_url').
                    '/testes-tecnicos/'.
                    $technicalAssessment->slug,
                'lastmod' => $technicalAssessment->updated_at->format('Y-m-d'),
            ];
        });

        return array_merge(
            $workshopArray->toArray(),
            $challengeArray->toArray(),
            $blogPostArray->toArray(),
            $technicalAssessmentsArray->toArray(),
            $submissionArray,
            $workshopLessonsArray,
            $challengesWithTutorialArray // /codigo e /tutorial dos MPs que possuem aula.
        );
    }
}
