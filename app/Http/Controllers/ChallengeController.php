<?php

namespace App\Http\Controllers;

use App\Events\ChallengeCompleted;
use App\Events\ChallengeForked;
use App\Events\ChallengeJoined;
use App\Http\Resources\ChallengeCardResource;
use App\Http\Resources\ChallengeResource;
use App\Http\Resources\ChallengeUserResource;
use App\Http\Resources\UserAvatarResource;
use App\Mail\UserJoinedChallenge;
use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Models\User;
use App\Services\ChallengeRepository;
use Github\ResultPager;
use GrahamCampbell\GitHub\Facades\GitHub;
use GrahamCampbell\GitHub\GitHubManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use App\Helpers\CacheKeyBuilder;

class ChallengeController extends Controller
{
    protected $client;

    protected $paginator;

    public function __construct(GitHubManager $manager)
    {
        // TODO - tirar do constructor
        $this->client = $manager->connection();
        $this->paginator = new ResultPager($this->client);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $challengeRepository = new ChallengeRepository();
        $query = $challengeRepository->challengeCardsQuery($user);
        $techFilter = $request->input('tecnologia');
        $textQuery = $request->input('q');
        $difficultyFilter = $request->input('dificuldade');
        $freeFilter = $request->input('gratuito');

        $orderBy = $request->input('ordenacao');

        $totalChallenges = $query->count();

        // Filtro de Tecnologia (tags)
        if ($techFilter) {
            $query->whereHas('mainTechnology', function ($subquery) use (
                $techFilter
            ) {
                $subquery->where('slug', $techFilter);
            });
        }
        if ($textQuery) {
            $query->where('name', 'like', '%'.$textQuery.'%');
        }
        
        if ($difficultyFilter) {
            $query->where('difficulty', $difficultyFilter);
        }
        
        if ($freeFilter) {
            $query->where('is_premium', $freeFilter == 'true' ? false : true);
        }
        
        if ($orderBy) {
            $query->orderByCustom($orderBy);
        }


        // se há user logado, não vamos pegar cachear e vamos adicionar o
        // current_user_id (para evitar muitas chamadas ao banco de dados)
        if ($user) {
            $challenges = $query->get();
            $challenges->each(function ($challenge) use ($user) {
                $challenge->current_user_id = $user->id;
            });
        } else {
            $challenges = Cache::remember(
                CacheKeyBuilder::buildCacheKey("challenges", [$techFilter, $textQuery, $difficultyFilter, $freeFilter, $orderBy]),
            1800,
            function () use ($query) {
                return $query->get();
            }
        );
        }
        
        $featuredChallenge = $challengeRepository->getFeaturedChallenge($user);

        return [
            'data' => [
                'challenges' => ChallengeCardResource::collection($challenges),
                'featuredChallenge' => $featuredChallenge
                    ? new ChallengeCardResource($featuredChallenge)
                    : null,
                'totalChallenges' => $totalChallenges,
            ],
        ];
    }

    public function hasForkedRepo(Request $request, $slug)
    {
        $user = $request->user();
        $challenge = Challenge::where('slug', $slug)->firstOrFail();
        $challengeUser = $challenge
            ->users()
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($challengeUser->pivot->fork_url) {
            return response()->json(['data' => true]);
        }

        try {
            $repositoryApi = $this->client->repo()->forks();
            // get all forks paginated
            $forks = $this->paginator->fetchAll($repositoryApi, 'all', [
                'codante-io',
                $challenge->repository_name,
            ]);

            //verify if the user has forked the repo
            $userFork = collect($forks)
                ->filter(function ($fork) use ($user) {
                    return $fork['owner']['login'] == $user->github_user;
                })
                ->first();

            if ($userFork) {
                // update challengeUser record with the fork url
                $challengeUser->pivot->fork_url = $userFork['html_url'];
                $challengeUser->pivot->save();

                event(new ChallengeForked($challengeUser, $challenge, $user));

                return response()->json(['data' => true]);
            }

            return response()->json(['data' => false]);
        } catch (\Exception $e) {
            return response()->json(['data' => false]);
        }
    }

    public function show($slug)
    {
        $challenge = $this->getChallenge($slug);

        $cacheKey = 'challenge_'.$challenge->slug;
        $cacheTime = 60 * 60; // 1 hour
        $repoInfo = cache()->remember($cacheKey, $cacheTime, function () use (
            $challenge
        ) {
            try {
                $repoInfo = GitHub::repo()->show(
                    'codante-io',
                    $challenge->repository_name
                );
            } catch (\Exception $e) {
                $repoInfo = [
                    'stargazers_count' => 0,
                    'forks_count' => 0,
                ];
            }

            return [
                'stargazers_count' => $repoInfo['stargazers_count'],
                'forks_count' => $repoInfo['forks_count'],
            ];
        });

        // add stars and forks to the challenges
        if ($repoInfo) {
            $challenge->stars = $repoInfo['stargazers_count'];
            $challenge->forks = $repoInfo['forks_count'];
        }

        return new ChallengeResource($challenge);
    }

    public function join(Request $request, $slug)
    {
        if (! $request->user()) {
            return response()->json(['error' => 'You are not logged in'], 403);
        }
        $challenge = Challenge::where('slug', $slug)->firstOrFail();
        $challenge->users()->syncWithoutDetaching($request->user()->id);

        // Get challenge user to send to event
        $challengeUser = $challenge
            ->users()
            ->where('user_id', $request->user()->id)
            ->first();

        event(
            new ChallengeJoined($challengeUser, $challenge, $request->user())
        );

        // send email
        Mail::to($request->user()->email)->send(
            new UserJoinedChallenge($request->user(), $challenge)
        );

        return response()->json(['ok' => true], 200);
    }

    public function userJoined(Request $request, $slug)
    {
        $challengeUser = ChallengeUser::whereHas('challenge', function (
            $query
        ) use ($slug) {
            $query->where('slug', $slug);
        })
            ->with('user')
            ->with('challenge')
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return new ChallengeUserResource($challengeUser);
    }

    public function updateChallengeUser(Request $request, $slug)
    {
        //only the user who joined the challenge can update their own data
        $challengeUser = $this->userJoined($request, $slug);

        if (! $challengeUser) {
            return response()->json(
                ['error' => 'You did not join this challenge'],
                403
            );
        }

        $challenge = Challenge::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'completed' => 'nullable|boolean',
            'joined_discord' => 'nullable|boolean',
            'fork_url' => 'nullable|url',
        ]);

        $challenge
            ->users()
            ->updateExistingPivot($request->user()->id, $validated);

        return response()->json(['ok' => true], 200);
    }

    public function getChallengeParticipantsBanner(Request $request, $slug)
    {
        $challenge = Challenge::where('slug', $slug)->firstOrFail();
        $participantsCount = $challenge->users()->count();
        $participantsInfo = $challenge
            ->users()
            ->select(
                'users.avatar_url',
                'users.name',
                'users.is_pro',
                'users.is_admin'
            )
            ->when(Auth::check(), function ($query) {
                $query->orderByRaw('users.id = ? DESC', [auth()->id()]);
            })
            ->get()
            ->take(20);

        return [
            'count' => $participantsCount,
            'avatars' => UserAvatarResource::collection($participantsInfo),
        ];
    }

    public function submit(Request $request, $slug)
    {
        // Validate the request
        $validated = $request->validate([
            'submission_url' => 'required|url',
            'metadata.twitter_username' => 'nullable',
            'metadata.rinha_largest_filename' => 'nullable',
        ]);

        // Check if the user has joined the challenge
        $challenge = Challenge::where('slug', $slug)->firstOrFail();
        $challengeUser = $challenge
            ->users()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($challengeUser->pivot['submitted_at']) {
            abort(400, 'Você já submeteu esse Mini Projeto');
        }

        // Check if the URL is not from a github repository
        if (Str::contains($validated['submission_url'], 'github.com')) {
            abort(
                400,
                'Você não pode adicionar o link do repositório. Adicione o link do deploy e tente novamente.'
            );
        }

        // Check if the URL is valid
        $response = \Illuminate\Support\Facades\Http::get(
            $validated['submission_url']
        );
        $status = $response->status();

        if ($status > 300) {
            abort(
                400,
                'Não conseguimos acessar a URL informada. Verifique e tente novamente.'
            );
        }

        $imagePath =
            "challenge-screenshots/$slug-$challengeUser->github_id-".
            Str::random(10).
            '.webp';
        $apiUrl = config('services.screenshot.base_url').'/screenshot';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.screenshot.token'),
            'Accept' => 'application/json',
        ])->post($apiUrl, [
            'url' => $validated['submission_url'],
            'fileName' => $imagePath,
        ]);

        if ($response->failed()) {
            abort(500, $response);
        }

        $data = $response->json();
        $imageUrl = $data['imageUrl'];

        // Saves in DB
        $challengeUser->pivot->submission_url = $validated['submission_url'];
        $challengeUser->pivot->metadata = $validated['metadata'] ?? null;
        $challengeUser->pivot->submission_image_url = $imageUrl;
        $challengeUser->pivot->submitted_at = now();
        $challengeUser->pivot->save();

        // Trigger event to award points
        event(
            new ChallengeCompleted($challengeUser, $challenge, $request->user())
        );
    }

    public function submitWithoutDeploy(Request $request, $slug)
    {
        // Validate the request
        $validated = $request->validate([
            'submission_image' => ['required', File::image()],
        ]);

        info($validated['submission_image']);

        $imgFile = $validated['submission_image'];

        // Check if the challenge exists
        $challenge = Challenge::where('slug', $slug)->firstOrFail();

        // Check if the user has joined the challenge
        $challengeUser = $challenge
            ->users()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Check if the user has already submitted
        if ($challengeUser->pivot['submitted_at']) {
            abort(400, 'Você já submeteu esse Mini Projeto');
        }

        $apiUrl = config('services.screenshot.base_url').'/upload-image';
        $imagePath =
            "challenge-screenshots/$slug-$challengeUser->github_id-".
            Str::random(10).
            '.webp';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.screenshot.token'),
            'Accept' => 'application/json',
        ])
            ->attach('submission_image', file_get_contents($imgFile->getPathname()), $imgFile->getClientOriginalName())
            ->attach('submission_path', $imagePath)
            ->post($apiUrl);

        if ($response->failed()) {
            abort(500, 'Erro ao submeter a imagem: '.$response);
        }

        $data = $response->json();
        $imageSubmissionUrl = $data['imageUrl'];

        // Saves in DB
        $challengeUser->pivot->submission_image_url = $imageSubmissionUrl;
        $challengeUser->pivot->metadata = $validated['metadata'] ?? null;
        $challengeUser->pivot->submitted_at = now();
        $challengeUser->pivot->save();

        // Trigger event to award points
        event(
            new ChallengeCompleted($challengeUser, $challenge, $request->user())
        );

        return $data;
    }

    public function updateSubmission(Request $request, $slug)
    {
        // Validate the request
        $validated = $request->validate([
            'submission_url' => 'required|url',
            'metadata.twitter_username' => 'nullable',
            'metadata.rinha_largest_filename' => 'nullable',
        ]);

        $challenge = Challenge::where('slug', $slug)->firstOrFail();
        $challengeUser = $challenge
            ->users()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Check if the user has joined the challenge
        $challenge = Challenge::where('slug', $slug)->firstOrFail();
        $challengeUser = $challenge
            ->users()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if (! $challengeUser->pivot['submission_url']) {
            abort(400, 'Não existe nenhuma submissão para ser atualizada.');
        }

        // if (
        //     $validated["submission_url"] ===
        //     $challengeUser->pivot["submission_url"]
        // ) {
        //     abort(400, "Adicione um link diferente do atual.");
        // }

        // Check if the URL is not from a github repository
        if (Str::contains($validated['submission_url'], 'github.com')) {
            abort(
                400,
                'Você não pode adicionar o link do repositório. Adicione o link do deploy e tente novamente.'
            );
        }

        $response = \Illuminate\Support\Facades\Http::get(
            $validated['submission_url']
        );
        $status = $response->status();

        if ($status > 300) {
            abort(
                400,
                'Não conseguimos acessar a URL informada. Verifique e tente novamente.'
            );
        }

        $imagePath =
            "challenge-screenshots/$slug-$challengeUser->github_id-".
            Str::random(10).
            '.webp';
        $apiUrl = config('services.screenshot.base_url').'/screenshot';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.screenshot.token'),
            'Accept' => 'application/json',
        ])->put($apiUrl, [
            'url' => $validated['submission_url'],
            'fileName' => $imagePath,
            'oldFilename' => $challengeUser->pivot->submission_image_url,
        ]);

        if ($response->failed()) {
            $error = $response->json();
            abort(500, 'API request failed: '.$error['message']);
        }

        $data = $response->json();
        $s3Location = $data['imageUrl'];

        // Saves in DB
        $challengeUser->pivot->submission_url = $validated['submission_url'];
        $challengeUser->pivot->metadata = $validated['metadata'] ?? null;
        $challengeUser->pivot->submission_image_url = $s3Location;
        $challengeUser->pivot->submitted_at = now();
        if ($challengeUser->pivot->listed == false) {
            $challengeUser->pivot->listed = true;
        }
        $challengeUser->pivot->save();
    }

    public function getSubmissions(Request $request, $slug)
    {
        $challenge = Challenge::where('slug', $slug)->firstOrFail();

        $challengeSubmissions = ChallengeUser::where(
            'challenge_id',
            $challenge->id
        )
            ->whereNotNull('submitted_at')
            ->orderBy('is_solution', 'desc')
            ->orderByRaw('user_id = ? DESC', auth()->id()) // Current user submission is first
            ->orderBy('submitted_at', 'desc')
            ->with(
                'user:id,name,avatar_url,github_user,is_pro,is_admin,linkedin_user'
            )
            ->get();

        return ChallengeUserResource::collection($challengeSubmissions);
    }

    public function getSubmissionFromGithubUser(
        Request $request,
        $slug,
        $githubUser
    ) {
        $challenge = Challenge::where('slug', $slug)
            ->select('id', 'name')
            ->firstOrFail();
        $user = User::where('github_user', $githubUser)
            ->select('id', 'name')
            ->firstOrFail();
        $challengeUser = ChallengeUser::where('challenge_id', $challenge->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return [
            'challenge_name' => $challenge->name,
            'user_name' => $user->name,
            'submission_image_url' => $challengeUser->submission_image_url,
        ];
    }

    private function getChallenge($slug)
    {
        $challenge = Challenge::where('slug', $slug)
            ->visible()
            ->with('workshop')
            ->with('workshop.lessons')
            ->with('workshop.instructor')
            ->withCount('users')
            ->with('tags')
            ->firstOrFail();

        return $challenge;
    }

}
