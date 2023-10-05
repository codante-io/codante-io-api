<?php

namespace App\Http\Controllers;

use App\Events\ChallengeCompleted;
use App\Events\ChallengeForked;
use App\Events\ChallengeJoined;
use App\Http\Resources\ChallengeCardResource;
use App\Http\Resources\ChallengeResource;
use App\Mail\UserJoinedChallenge;
use App\Models\Challenge;
use App\Models\Reaction;
use App\Models\Workshop;
use DB;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Github\ResultPager;
use GrahamCampbell\GitHub\Facades\GitHub;
use GrahamCampbell\GitHub\GitHubManager;
use Illuminate\Support\Facades\Auth;

class ChallengeController extends Controller
{
    protected $client;
    protected $paginator;

    public function __construct(GitHubManager $manager)
    {
        $this->client = $manager->connection();
        $this->paginator = new ResultPager($this->client);
    }

    public function index()
    {
        Auth::shouldUse("sanctum");

        $challenges = Challenge::query()
            ->select(
                "id",
                "name",
                "slug",
                "short_description",
                "image_url",
                "status",
                "difficulty"
            )
            ->where("status", "published")
            ->orWhere("status", "soon")
            ->with("workshop:id,challenge_id")
            ->withCount("users")
            ->with([
                "users" => function ($query) {
                    $query
                        ->select("users.id", "users.avatar_url", "users.is_pro")
                        ->inRandomOrder()
                        ->limit(5);
                },
            ])
            ->with("tags")
            ->orderBy("status", "asc")
            ->orderBy("position", "asc")
            ->orderBy("published_at", "desc")
            ->get();

        return ChallengeCardResource::collection($challenges);
    }

    public function hasForkedRepo(Request $request, $slug)
    {
        $user = $request->user();
        $challenge = Challenge::where("slug", $slug)->firstOrFail();
        $challengeUser = $challenge
            ->users()
            ->where("user_id", $user->id)
            ->firstOrFail();

        if ($challengeUser->pivot->fork_url) {
            return response()->json(["data" => true]);
        }

        try {
            $repositoryApi = $this->client->repo()->forks();
            # get all forks paginated
            $forks = $this->paginator->fetchAll($repositoryApi, "all", [
                "codante-io",
                $challenge->repository_name,
            ]);

            #verify if the user has forked the repo
            $userFork = collect($forks)
                ->filter(function ($fork) use ($user) {
                    return $fork["owner"]["login"] == $user->github_user;
                })
                ->first();

            if ($userFork) {
                # update challengeUser record with the fork url
                $challengeUser->pivot->fork_url = $userFork["html_url"];
                $challengeUser->pivot->save();

                event(new ChallengeForked($challengeUser, $challenge, $user));

                return response()->json(["data" => true]);
            }

            return response()->json(["data" => false]);
        } catch (\Exception $e) {
            return response()->json(["data" => false]);
        }
    }

    public function show($slug)
    {
        Auth::shouldUse("sanctum");

        // if not logged in, we show cached version
        if (!Auth::check()) {
            $challenge = $this->getChallenge($slug);
        } else {
            $challenge = $this->getChallengeWithCompletedLessons($slug);
        }
        // $challenge->current_user_is_enrolled = $challenge->userJoined();

        $cacheKey = "challenge_" . $challenge->slug;
        $cacheTime = 60 * 60; // 1 hour
        $repoInfo = cache()->remember($cacheKey, $cacheTime, function () use (
            $challenge
        ) {
            try {
                $repoInfo = GitHub::repo()->show(
                    "codante-io",
                    $challenge->repository_name
                );
            } catch (\Exception $e) {
                $repoInfo = [
                    "stargazers_count" => 0,
                    "forks_count" => 0,
                ];
            }

            return [
                "stargazers_count" => $repoInfo["stargazers_count"],
                "forks_count" => $repoInfo["forks_count"],
            ];
        });

        # add stars and forks to the challenges
        if ($repoInfo) {
            $challenge->stars = $repoInfo["stargazers_count"];
            $challenge->forks = $repoInfo["forks_count"];
        }

        return new ChallengeResource($challenge);
    }

    public function join(Request $request, $slug)
    {
        if (!$request->user()) {
            return response()->json(["error" => "You are not logged in"], 403);
        }
        $challenge = Challenge::where("slug", $slug)->firstOrFail();
        $challenge->users()->syncWithoutDetaching($request->user()->id);

        // Get challenge user to send to event
        $challengeUser = $challenge
            ->users()
            ->where("user_id", $request->user()->id)
            ->first();

        event(
            new ChallengeJoined($challengeUser, $challenge, $request->user())
        );

        // send email
        Mail::to($request->user()->email)->send(
            new UserJoinedChallenge($request->user(), $challenge)
        );

        return response()->json(["ok" => true], 200);
    }

    public function userJoined(Request $request, $slug)
    {
        $challenge = Challenge::where("slug", $slug)->firstOrFail();

        $challengeUser = $challenge
            ->users()
            ->where("user_id", $request->user()->id)
            ->firstOrFail();
        return $challengeUser;
    }

    public function updateChallengeUser(Request $request, $slug)
    {
        //only the user who joined the challenge can update their own data
        $challengeUser = $this->userJoined($request, $slug);

        if (!$challengeUser) {
            return response()->json(
                ["error" => "You did not join this challenge"],
                403
            );
        }

        $challenge = Challenge::where("slug", $slug)->firstOrFail();

        $validated = $request->validate([
            "completed" => "nullable|boolean",
            "joined_discord" => "nullable|boolean",
            "fork_url" => "nullable|url",
        ]);

        $challenge
            ->users()
            ->updateExistingPivot($request->user()->id, $validated);

        return response()->json(["ok" => true], 200);
    }

    public function getChallengeParticipantsBanner(Request $request, $slug)
    {
        $challenge = Challenge::where("slug", $slug)->firstOrFail();
        $participantsCount = $challenge->users()->count();
        $participantsInfo = $challenge
            ->users()
            ->get()
            ->map(function ($user) {
                return [
                    "avatar_url" => $user->avatar_url,
                    "is_pro" => $user->is_pro,
                ];
            })
            ->take(20);
        return [
            "count" => $participantsCount,
            "avatars" => $participantsInfo,
        ];
    }

    public function submit(Request $request, $slug)
    {
        // Validate the request
        $validated = $request->validate([
            "submission_url" => "required|url",
        ]);

        // Check if the user has joined the challenge
        $challenge = Challenge::where("slug", $slug)->firstOrFail();
        $challengeUser = $challenge
            ->users()
            ->where("user_id", $request->user()->id)
            ->firstOrFail();

        if ($challengeUser->pivot["submission_url"]) {
            abort(400, "Você já submeteu esse Mini Projeto");
        }

        // Check if the URL is valid
        $response = \Illuminate\Support\Facades\Http::get(
            $validated["submission_url"]
        );
        $status = $response->status();

        if ($status > 300) {
            abort(
                400,
                "Não conseguimos acessar a URL informada. Verifique e tente novamente."
            );
        }

        // Capture the screenshot
        $urlToCapture = $validated["submission_url"];
        $imagePath = "/challenges/$slug/$challengeUser->github_id.png";

        $screenshot = Browsershot::url($urlToCapture);
        // $screenshot->setNodeBinary(
        //     "/home/icaro/.nvm/versions/node/v18.4.0/bin/node"
        // );
        $screenshot->windowSize(1280, 720);
        $screenshot->setDelay(2000);
        $screenshot->setScreenshotType("png");
        $screenshot->optimize();
        $storageRes = Storage::disk("s3")->put(
            $imagePath,
            $screenshot->screenshot()
        );

        // Saves in DB
        $challengeUser->pivot->submission_url = $validated["submission_url"];
        $challengeUser->pivot->submission_image_url = Storage::disk("s3")->url(
            $imagePath
        );
        $challengeUser->pivot->submitted_at = now();
        $challengeUser->pivot->save();

        // Trigger event to award points
        event(
            new ChallengeCompleted($challengeUser, $challenge, $request->user())
        );
    }

    public function getSubmissions(Request $request, $slug)
    {
        $challenge = Challenge::where("slug", $slug)->firstOrFail();

        $challengeUsers = $challenge
            ->users()
            ->select("users.name", "users.avatar_url", "users.github_user", "users.is_pro")
            ->wherePivotNotNull("submission_url")
            ->orderBy("submitted_at", "desc")
            ->get();

        $submissions = $challengeUsers->map(function ($challengeUser) {
            return [
                "id" => $challengeUser->pivot->id,
                "user_name" => $challengeUser->name,
                "user_avatar_url" => $challengeUser->avatar_url,
                "user_github_user" => $challengeUser->github_user,
                "submission_url" => $challengeUser->pivot->submission_url,
                "fork_url" => $challengeUser->pivot->fork_url,
                "is_pro" => $challengeUser->is_pro,
                "submission_image_url" =>
                $challengeUser->pivot->submission_image_url,
                "reactions" => Reaction::getReactions(
                    "App\\Models\\ChallengeUser",
                    $challengeUser->pivot->id
                ),
            ];
        });

        return response()->json(["data" => $submissions]);
    }
    private function getChallenge($slug)
    {
        $challenge = Challenge::where("slug", $slug)
            ->where("status", "published")
            ->with("workshop")
            ->with("workshop.lessons")
            ->with("workshop.instructor")
            ->withCount("users")
            ->with("tags")
            ->firstOrFail();

        return $challenge;
    }

    private function getChallengeWithCompletedLessons($slug)
    {
        $challenge = Challenge::where("slug", $slug)
            ->where("status", "published")
            ->with("workshop")
            ->with("workshop.lessons")
            ->with("workshop.instructor")
            ->with([
                "workshop",
                "workshop.lessons",
                "workshop.lessons.users" => function ($query) {
                    $query
                        ->select("users.id")
                        ->where("user_id", Auth::guard("sanctum")->id());
                },
            ])
            ->withCount("users")
            ->with("tags")
            ->firstOrFail();

        if (
            !$challenge->workshop ||
            $challenge->workshop->lessons->count() === 0
        ) {
            return $challenge;
        }

        $challenge->workshop->lessons->each(function ($lesson) {
            $lesson->user_completed = $lesson->users->count() > 0;
            unset($lesson->users);
        });

        return $challenge;
    }
}
