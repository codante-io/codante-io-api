<?php

declare(strict_types=1);

namespace Tests\Feature\Pagarme;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class CodandoWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('discord.channels.teste', 'https://discord.test/webhook');
        config()->set('services.pagarme.api_key', 'test-key');
    }

    public function testCodandoOrderWebhookLifecycle(): void
    {
        $plan = Plan::firstWhere('slug', 'codando-com-ia-v1');
        $this->assertNotNull($plan);

        Mail::fake();

        Http::fake([
            'https://discord.test/*' => Http::response([], 204),
        ]);

        $orderId = 'ord_codando';

        // order.created
        $this->postJson('/api/pagarme/notification', [
            'type' => 'order.created',
            'data' => [
                'id' => $orderId,
                'status' => 'pending',
                'amount' => 58800,
                'metadata' => [
                    'product_slug' => 'curso-ao-vivo-codando-com-ia-v1',
                ],
                'customer' => [
                    'name' => 'Webhook Tester',
                    'email' => 'webhook@example.com',
                    'metadata' => [
                        'product_slug' => 'curso-ao-vivo-codando-com-ia-v1',
                    ],
                    'phones' => [
                        'mobile_phone' => [
                            'country_code' => '55',
                            'area_code' => '11',
                            'number' => '988887777',
                        ],
                    ],
                ],
                'items' => [
                    [
                        'description' => 'Codando com IA (Ao Vivo) v1',
                        'amount' => 58800,
                        'quantity' => 1,
                    ],
                ],
                'charges' => [],
            ],
        ])->assertOk();

        $user = User::firstWhere('email', 'webhook@example.com');
        $this->assertNotNull($user);
        $this->assertSame('Webhook Tester', $user->name);
        $this->assertSame('11988887777', $user->mobile_phone);
        $this->assertFalse($user->is_pro);

        $subscription = Subscription::firstWhere('provider_id', $orderId);
        $this->assertNotNull($subscription);
        $this->assertSame('pending', $subscription->status);
        $this->assertNull($subscription->payment_method);
        $this->assertSame($plan->id, $subscription->plan_id);

        // order.closed with pix payment
        $this->postJson('/api/pagarme/notification', [
            'type' => 'order.closed',
            'data' => [
                'id' => $orderId,
                'status' => 'paid',
                'amount' => 58800,
                'metadata' => [
                    'product_slug' => 'curso-ao-vivo-codando-com-ia-v1',
                ],
                'customer' => [
                    'name' => 'Webhook Tester',
                    'email' => 'webhook@example.com',
                    'metadata' => [
                        'product_slug' => 'curso-ao-vivo-codando-com-ia-v1',
                    ],
                ],
                'items' => [
                    [
                        'description' => 'Codando com IA (Ao Vivo) v1',
                        'amount' => 58800,
                        'quantity' => 1,
                    ],
                ],
                'charges' => [
                    [
                        'payment_method' => 'pix',
                        'last_transaction' => [
                            'qr_code' => 'PIXCODE123',
                            'qr_code_url' => 'https://pix.example/qrcode',
                        ],
                    ],
                ],
            ],
        ])->assertOk();

        $subscription->refresh();
        $user->refresh();

        $this->assertSame('active', $subscription->status);
        $this->assertSame('pix', $subscription->payment_method);
        $this->assertSame('PIXCODE123', $subscription->boleto_barcode);
        $this->assertSame('https://pix.example/qrcode', $subscription->boleto_url);
        $this->assertFalse($user->is_pro);

        // order.canceled
        $this->postJson('/api/pagarme/notification', [
            'type' => 'order.canceled',
            'data' => [
                'id' => $orderId,
                'status' => 'canceled',
                'amount' => 58800,
                'metadata' => [
                    'product_slug' => 'curso-ao-vivo-codando-com-ia-v1',
                ],
                'customer' => [
                    'name' => 'Webhook Tester',
                    'email' => 'webhook@example.com',
                    'metadata' => [
                        'product_slug' => 'curso-ao-vivo-codando-com-ia-v1',
                    ],
                ],
                'items' => [
                    [
                        'description' => 'Codando com IA (Ao Vivo) v1',
                        'amount' => 58800,
                        'quantity' => 1,
                    ],
                ],
                'charges' => [
                    [
                        'payment_method' => 'pix',
                    ],
                ],
            ],
        ])->assertOk();

        $subscription->refresh();
        $user->refresh();

        $this->assertSame('canceled', $subscription->status);
        $this->assertFalse($user->is_pro);

        Mail::assertNothingSent();
    }

    public function testWebhookDoesNotCreateSubscriptionForOtherProducts(): void
    {
        Mail::fake();

        Http::fake([
            'https://discord.test/*' => Http::response([], 204),
        ]);

        // Webhook de outro produto (não Codando com IA)
        $this->postJson('/api/pagarme/notification', [
            'type' => 'order.created',
            'data' => [
                'id' => 'ord_other_product',
                'status' => 'pending',
                'amount' => 10000,
                'customer' => [
                    'name' => 'Other Product User',
                    'email' => 'other@example.com',
                    'metadata' => [],
                ],
                'items' => [
                    [
                        'description' => 'Other Product',
                        'amount' => 10000,
                        'quantity' => 1,
                    ],
                ],
                'charges' => [],
            ],
        ])->assertOk();

        // Verifica que usuário NÃO foi criado
        $user = User::where('email', 'other@example.com')->first();
        $this->assertNull($user);

        // Verifica que subscription NÃO foi criada
        $subscription = Subscription::where('provider_id', 'ord_other_product')->first();
        $this->assertNull($subscription);
    }
}
