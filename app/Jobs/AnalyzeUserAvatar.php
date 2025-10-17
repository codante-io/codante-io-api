<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class AnalyzeUserAvatar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const OPENAI_API_URL = 'https://api.openai.com/v1/chat/completions';
    private const OPENAI_MODEL = 'gpt-4o-mini';

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 60;

    public function __construct(
        private int $userId,
        private string $avatarUrl
    ) {}

    public function handle(): void
    {
        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            Log::error('AnalyzeUserAvatar: OpenAI API key not configured', [
                'user_id' => $this->userId,
            ]);
            return;
        }

        try {
            // Convert and optimize image before sending to OpenAI
            $optimizedImageData = $this->convertAndOptimizeImage($this->avatarUrl);

            if ($optimizedImageData === null) {
                Log::warning('AnalyzeUserAvatar: Failed to process image', [
                    'user_id' => $this->userId,
                    'avatar_url' => $this->avatarUrl,
                ]);
                $this->updateUserSettings(['type' => 'unsupported', 'confidence' => 1.0]);
                return;
            }

            $result = $this->analyzeAvatar($optimizedImageData, $apiKey);
            $this->updateUserSettings($result);

            Log::info('AnalyzeUserAvatar: Avatar analyzed successfully', [
                'user_id' => $this->userId,
                'type' => $result['type'],
                'confidence' => $result['confidence'],
            ]);
        } catch (\Exception $e) {
            Log::error('AnalyzeUserAvatar: Failed to analyze avatar', [
                'user_id' => $this->userId,
                'avatar_url' => $this->avatarUrl,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    private function convertAndOptimizeImage(string $url): ?string
    {
        try {
            // Download image
            $imageContent = Http::timeout(10)->get($url)->body();

            if (empty($imageContent)) {
                return null;
            }

            // Create image from string
            $image = imagecreatefromstring($imageContent);

            if ($image === false) {
                return null;
            }

            // Get original dimensions
            $width = imagesx($image);
            $height = imagesy($image);

            // Resize to max 256px (reduces tokens significantly)
            $maxSize = 256;
            if ($width > $maxSize || $height > $maxSize) {
                $ratio = min($maxSize / $width, $maxSize / $height);
                $newWidth = (int) ($width * $ratio);
                $newHeight = (int) ($height * $ratio);

                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resized;
            }

            // Convert to JPEG with compression
            ob_start();
            imagejpeg($image, null, 85); // 85% quality
            $jpegData = ob_get_clean();
            imagedestroy($image);

            // Convert to base64 data URL
            return 'data:image/jpeg;base64,' . base64_encode($jpegData);
        } catch (\Exception $e) {
            Log::error('AnalyzeUserAvatar: Failed to convert image', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function analyzeAvatar(string $avatarUrl, string $apiKey): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post(self::OPENAI_API_URL, [
            'model' => self::OPENAI_MODEL,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Analyze this avatar image and determine if it is a real photograph of a person or a GitHub-generated identicon (geometric/pixelated pattern). Provide your assessment with a confidence score.',
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $avatarUrl,
                            ],
                        ],
                    ],
                ],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'avatar_analysis',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'type' => [
                                'type' => 'string',
                                'enum' => ['real', 'generated'],
                                'description' => 'Whether this is a real photo or a generated identicon',
                            ],
                            'confidence' => [
                                'type' => 'number',
                                'description' => 'Confidence score between 0 and 1',
                            ],
                        ],
                        'required' => ['type', 'confidence'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'max_tokens' => 100,
        ]);

        if (! $response->successful()) {
            throw new \Exception('OpenAI API request failed: ' . $response->body());
        }

        $content = $response->json('choices.0.message.content');

        // Parse JSON response (guaranteed to be valid JSON with structured outputs)
        $result = json_decode($content, true);

        if (! is_array($result) || ! isset($result['type']) || ! isset($result['confidence'])) {
            throw new \Exception('Invalid response format from OpenAI: ' . $content);
        }

        return [
            'type' => $result['type'],
            'confidence' => (float) $result['confidence'],
        ];
    }

    private function updateUserSettings(array $analysis): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            Log::warning('AnalyzeUserAvatar: User not found', [
                'user_id' => $this->userId,
            ]);
            return;
        }

        $settings = $user->settings ?? [];
        $settings['avatar_analysis'] = [
            'type' => $analysis['type'],
            'confidence' => $analysis['confidence'],
            'analyzed_at' => now()->toDateTimeString(),
        ];

        $user->settings = $settings;
        $user->save();
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AnalyzeUserAvatar: Job failed after all retries', [
            'user_id' => $this->userId,
            'avatar_url' => $this->avatarUrl,
            'error' => $exception->getMessage(),
        ]);
    }
}
