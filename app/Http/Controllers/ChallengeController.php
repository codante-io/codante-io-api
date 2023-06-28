<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChallengeResource;
use App\Mail\UserJoinedChallenge;
use App\Models\Challenge;
use App\Models\Reaction;
use App\Models\Workshop;
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
        return ChallengeResource::collection(
            Challenge::query()
                ->where("status", "published")
                ->orWhere("status", "soon")
                ->with("workshop")
                ->with("workshop.lessons")
                ->withCount("users")
                ->with("users")
                ->with("tags")
                ->get()
        );
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
            $repositoryApi = $this->client->api("repo")->forks();
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

                return response()->json(["data" => true]);
            }

            return response()->json(["data" => false]);
        } catch (\Exception $e) {
            return response()->json(["data" => false]);
        }
    }

    public function show($slug)
    {
        // if not logged in, we show cached version
        if (!Auth::guard("sanctum")->check()) {
            $challenge = $this->getChallenge($slug);
        } else {
            $challenge = $this->getChallengeWithCompletedLessons($slug);
        }

        $cacheKey = "challenge_" . $challenge->slug;
        $cacheTime = 60 * 60; // 1 hour
        $repoInfo = cache()->remember($cacheKey, $cacheTime, function () use (
            $challenge
        ) {
            try {
                $repoInfo = GitHub::repo()->show(
                    "codante-io",
                    $challenge->slug
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

        return response()->json(["data" => $challenge]);
    }

    public function join(Request $request, $slug)
    {
        if (!$request->user()) {
            return response()->json(["error" => "You are not logged in"], 403);
        }
        $challenge = Challenge::where("slug", $slug)->firstOrFail();
        $challenge->users()->syncWithoutDetaching($request->user()->id);

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
        $participantsAvatars = $challenge
            ->users()
            ->get()
            ->map(function ($user) {
                return $user->avatar_url;
            })
            ->take(20);
        return [
            "count" => $participantsCount,
            "avatars" => $participantsAvatars,
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
        $challengeUser->pivot->save();
    }

    public function getSubmissions(Request $request, $slug)
    {
        $challenge = Challenge::where("slug", $slug)->firstOrFail();

        $challengeUsers = $challenge
            ->users()
            ->select("users.name", "users.avatar_url", "users.github_user")
            ->wherePivotNotNull("submission_url")
            ->get();

        $submissions = $challengeUsers->map(function ($user) {
            return [
                "id" => $user->pivot->id,
                "user_name" => $user->name,
                "user_avatar_url" => $user->avatar_url,
                "user_github_user" => $user->github_user,
                "submission_url" => $user->pivot->submission_url,
                "submission_image_url" => $user->pivot->submission_image_url,
                "reactions" => Reaction::getReactions(
                    "App\\Models\\ChallengeUser",
                    $user->pivot->id
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
