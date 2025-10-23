<?php declare(strict_types=1);

namespace App\Services\Mail;

use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Dedicated integration with the EmailOctopus v2 API.
 * Keep v1.6 code paths intact by opting in only where required.
 *
 * @see https://emailoctopus.com/api-documentation/v2
 */
final class EmailOctopusV2Service
{
    private string $apiKey;

    private string $listId = '4a67da48-0ed2-11ee-988e-5101d064b06e';

    private string $baseUrl = 'https://api.emailoctopus.com';

    public function __construct()
    {
        $this->apiKey = (string) config('services.email_octopus.api_key');
    }

    public function registerEmailOctopusContact(string $email, array $fields = [], array $tags = []): void
    {
        $payload = [
            'email_address' => $email,
            'status' => 'subscribed',
        ];

        $fields = $this->filterFields($fields);
        if (! empty($fields)) {
            $payload['fields'] = $fields;
        }

        $tagsObject = $this->normalizeTagsObject($tags);
        if (! empty($tagsObject)) {
            $payload['tags'] = $tagsObject;
        }

        $response = $this->request()->put(
            $this->buildUrl("/lists/{$this->listId}/contacts"),
            $payload,
        );

        $this->logFailureIfNeeded('create', $payload, $response->status(), $response->body());
    }

    public function updateEmailOctopusContact(string $email, array $fields = [], array $tags = []): void
    {
        $payload = [
            'email_address' => $email,
        ];

        $fields = $this->filterFields($fields);
        if (! empty($fields)) {
            $payload['fields'] = $fields;
        }

        $tagsObject = $this->normalizeTagsObject($tags);
        if (! empty($tagsObject)) {
            $payload['tags'] = $tagsObject;
        }

        $response = $this->request()->put(
            $this->buildUrl("/lists/{$this->listId}/contacts/{$this->hashEmail($email)}"),
            $payload,
        );

        $this->logFailureIfNeeded('update', $payload, $response->status(), $response->body());
    }

    public function updateLeadAfterSignUp(User $user, array $tags = []): void
    {
        [$firstName, $lastName] = $this->splitName($user->name);

        $this->updateEmailOctopusContact(
            $user->email,
            [
                'FirstName' => $firstName,
                'LastName' => $lastName,
                'is_pro' => false,
                'is_registered_user' => true,
            ],
            $tags,
        );
    }

    public function createLead(string $email, array $tags = [], ?string $firstName = null, ?string $lastName = null): void
    {
        $this->registerEmailOctopusContact(
            $email,
            [
                'is_registered_user' => false,
                'is_pro' => false,
                'FirstName' => $firstName,
                'LastName' => $lastName,
            ],
            $tags,
        );
    }

    public function updateLead(string $email, array $tags = []): void
    {
        $this->updateEmailOctopusContact($email, [], $tags);
    }

    public function addUser(User $user): void
    {
        [$firstName, $lastName] = $this->splitName($user->name);

        $this->registerEmailOctopusContact(
            $user->email,
            [
                'FirstName' => $firstName,
                'LastName' => $lastName,
                'is_pro' => false,
                'is_registered_user' => true,
            ],
        );
    }

    public function updateUser(User $user): void
    {
        [$firstName, $lastName] = $this->splitName($user->name);

        $this->updateEmailOctopusContact(
            $user->email,
            [
                'FirstName' => $firstName,
                'LastName' => $lastName,
                'is_pro' => $user->is_pro,
            ],
        );
    }

    public function deleteUser(User $user): void
    {
        $response = $this->request()->delete(
            $this->buildUrl("/lists/{$this->listId}/contacts/{$this->hashEmail($user->email)}"),
        );

        $this->logFailureIfNeeded('delete', ['email' => $user->email], $response->status(), $response->body());
    }

    private function request(): PendingRequest
    {
        return Http::withToken($this->apiKey)->acceptJson()->asJson();
    }

    private function buildUrl(string $path): string
    {
        return rtrim($this->baseUrl, '/').$path;
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function splitName(?string $fullName): array
    {
        if ($fullName === null || trim($fullName) === '') {
            return [null, null];
        }

        $parts = preg_split('/\s+/', trim($fullName)) ?: [];

        if ($parts === []) {
            return [null, null];
        }

        $firstName = $parts[0];
        $lastName = $parts[count($parts) - 1];

        return [$firstName, $lastName];
    }

    private function filterFields(array $fields): array
    {
        $filtered = [];

        foreach ($fields as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_bool($value)) {
                $value = $value ? 1 : 0;
            }

            $filtered[$key] = $value;
        }

        return $filtered;
    }

    /**
     * @return string[]
     */
    private function normalizeTagsArray(array $tags): array
    {
        $normalized = array_filter(
            array_map(
                static fn ($tag) => is_string($tag) ? trim($tag) : '',
                $tags,
            ),
            static fn (string $tag) => $tag !== '',
        );

        return array_values($normalized);
    }

    /**
     * @return array<string, bool>
     */
    private function normalizeTagsObject(array $tags): array
    {
        $normalized = [];

        foreach ($this->normalizeTagsArray($tags) as $tag) {
            $normalized[$tag] = true;
        }

        return $normalized;
    }

    private function hashEmail(string $email): string
    {
        return md5(strtolower(trim($email)));
    }

    private function logFailureIfNeeded(string $operation, array $payload, int $status, ?string $body): void
    {
        if ($status < 400) {
            return;
        }

        logger()->error('EmailOctopus v2 request failed', [
            'operation' => $operation,
            'status' => $status,
            'payload' => $payload,
            'response' => $body,
        ]);
    }
}
