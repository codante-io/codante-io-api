<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\AnalyzeUserAvatar;
use App\Models\User;
use Illuminate\Support\Facades\Log;

final class DetectGitHubGeneratedAvatars
{
    private const BATCH_SIZE = 50;

    public function handle(): void
    {
        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            Log::error('DetectGitHubGeneratedAvatars: OpenAI API key not configured');
            return;
        }

        $users = $this->getUsersToAnalyze();

        if ($users->isEmpty()) {
            Log::info('DetectGitHubGeneratedAvatars: No users to analyze');
            return;
        }

        Log::info('DetectGitHubGeneratedAvatars: Dispatching analysis jobs', [
            'user_count' => $users->count(),
        ]);

        $dispatched = 0;

        foreach ($users as $user) {
            // Dispatch job to queue for parallel processing
            AnalyzeUserAvatar::dispatch($user->id, $user->avatar_url);

            $dispatched++;
        }

        Log::info('DetectGitHubGeneratedAvatars: Jobs dispatched successfully', [
            'dispatched' => $dispatched,
        ]);
    }

    private function getUsersToAnalyze()
    {
        return User::whereNotNull('avatar_url')
            ->where(function ($query) {
                $query
                    ->whereRaw("JSON_EXTRACT(settings, '$.avatar_analysis') IS NULL")
                    ->orWhereRaw(
                        "JSON_UNQUOTE(JSON_EXTRACT(settings, '$.avatar_analysis.type')) = ?",
                        ['unknown']
                    );
            })
            ->inRandomOrder()
            ->limit(self::BATCH_SIZE)
            ->get(['id', 'avatar_url', 'settings']);
    }
}
