<?php declare(strict_types=1);

namespace Tests\Feature\Leads;

use App\Events\LeadRegistered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CodandoLeadEmailOctopusV2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.email_octopus.api_key', 'test-key');
    }

    public function testLeadCreationWithCodandoTagUsesEmailOctopusV2Upsert(): void
    {
        Event::fake([LeadRegistered::class]);

        Http::fake([
            'https://api.emailoctopus.com/*' => Http::response([], 200),
        ]);

        $payload = [
            'email' => 'codando@example.com',
            'name' => 'Codando Tester',
            'phone' => '(11) 99999-9999',
            'tags' => ['curso-ao-vivo-codando-com-ia-v1-assinante-codante'],
        ];

        $response = $this->postJson('/api/leads', $payload);

        $response->assertOk()->assertJson([
            'message' => 'Lead cadastrado com sucesso',
        ]);

        $this->assertDatabaseHas('leads', [
            'email' => 'codando@example.com',
            'tag' => 'curso-ao-vivo-codando-com-ia-v1-assinante-codante',
        ]);

        Http::assertSentCount(1);
        Http::assertSent(function ($request) {
            $isPut = $request->method() === 'PUT';
            $targetsContactsEndpoint = Str::endsWith($request->url(), '/contacts');
            $payload = $request->data();
            $hasExpectedTag = isset($payload['tags']['curso-ao-vivo-codando-com-ia-v1-assinante-codante']) &&
                $payload['tags']['curso-ao-vivo-codando-com-ia-v1-assinante-codante'] === true;

            return $isPut && $targetsContactsEndpoint && $hasExpectedTag;
        });
    }
}
