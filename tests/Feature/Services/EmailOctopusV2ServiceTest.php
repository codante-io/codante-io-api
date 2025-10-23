<?php declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\Mail\EmailOctopusV2Service;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class EmailOctopusV2ServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.email_octopus.api_key', 'test-key');
    }

    public function testCreateLeadSendsPutWithExpectedPayload(): void
    {
        $capturedRequest = null;

        Http::fake([
            'https://api.emailoctopus.com/*' => function ($request) use (&$capturedRequest) {
                $capturedRequest = $request;

                return Http::response([], 200);
            },
        ]);

        $service = new EmailOctopusV2Service();
        $service->createLead(
            'test@example.com',
            ['my-tag'],
            'Test',
            'User',
        );

        $this->assertNotNull($capturedRequest);
        $this->assertSame('PUT', $capturedRequest->method());
        $this->assertSame(
            'https://api.emailoctopus.com/lists/4a67da48-0ed2-11ee-988e-5101d064b06e/contacts',
            $capturedRequest->url()
        );

        $payload = $capturedRequest->data();

        $this->assertSame('test@example.com', $payload['email_address']);
        $this->assertSame('subscribed', $payload['status']);
        $this->assertSame([
            'is_registered_user' => 0,
            'is_pro' => 0,
            'FirstName' => 'Test',
            'LastName' => 'User',
        ], $payload['fields']);
        $this->assertSame([
            'my-tag' => true,
        ], $payload['tags']);
    }

    public function testUpdateLeadSendsPutWithTagsObject(): void
    {
        $capturedRequest = null;

        Http::fake([
            'https://api.emailoctopus.com/*' => function ($request) use (&$capturedRequest) {
                $capturedRequest = $request;

                return Http::response([], 200);
            },
        ]);

        $service = new EmailOctopusV2Service();
        $service->updateLead(
            'test@example.com',
            ['tag-one', 'tag-two'],
        );

        $this->assertNotNull($capturedRequest);
        $this->assertSame('PUT', $capturedRequest->method());
        $this->assertSame(
            'https://api.emailoctopus.com/lists/4a67da48-0ed2-11ee-988e-5101d064b06e/contacts/'.md5('test@example.com'),
            $capturedRequest->url(),
        );

        $payload = $capturedRequest->data();

        $this->assertArrayNotHasKey('fields', $payload);
        $this->assertSame([
            'tag-one' => true,
            'tag-two' => true,
        ], $payload['tags']);
    }
}
