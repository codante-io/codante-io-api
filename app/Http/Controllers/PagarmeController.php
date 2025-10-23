<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCheckoutLinkV2Request;
use App\Http\Resources\SubscriptionResource;
use App\Mail\PaymentConfirmed;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class PagarmeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only([
            'createOrderAndGetCheckoutLink',
            'getSubscriptionByPagarmeOrderId',
        ]);
    }

    public function createOrderAndGetCheckoutLink(Request $request)
    {

        $plan_id = $request->plan_id ?? 1;
        $user = Auth::user();
        $plan = Plan::find($plan_id);

        $planDetails = json_decode($plan->details);

        $promoPrice =
            $plan->price_in_cents +
            $planDetails->content_count * 100 +
            $planDetails->user_raised_count * 10 * 100;

        $couponCode = $request->coupon;
        $coupon = (new Coupon)->getValidCoupon($couponCode, $plan_id);

        if ($coupon) {
            $promoPrice =
                $coupon->type === 'percentage'
                ? $promoPrice -
                ($promoPrice * $coupon->discount_amount) / 100
                : $promoPrice - $coupon->discount_amount;
        }

        $endpoint = 'https://api.pagar.me/core/v5/orders';
        // to get pagarme checkout link we need to create an order:

        $response = Http::withBasicAuth(
            config('services.pagarme.api_key'),
            ''
        )->post($endpoint, [
            'customer' => [
                'name' => $user->name,
                'email' => $user->email,
                'code' => $user->id,
                'metadata' => [
                    'coupon_code' => $coupon ? $coupon->code : null,
                ],
            ],
            'items' => [
                [
                    'id' => '1',
                    'amount' => $promoPrice,
                    'description' => $plan->name,
                    'quantity' => 1,
                    'code' => $plan->id,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'checkout',
                    'checkout' => [
                        'customer_editable' => true,
                        'accepted_payment_methods' => [
                            'credit_card',
                            'boleto',
                            'pix',
                        ],
                        'success_url' => config('app.frontend_url') . '/assine/sucesso',
                        'pix' => [
                            'expires_in' => 86400,
                        ],
                        'boleto' => [
                            'due_at' => Carbon::now()
                                ->addDays(3)
                                ->toIso8601String(),
                        ],
                        'credit_card' => [
                            'installments' => [
                                [
                                    'number' => 1,
                                    'total' => $promoPrice,
                                ],
                                [
                                    'number' => 2,
                                    'total' => $promoPrice,
                                ],
                                [
                                    'number' => 3,
                                    'total' => $promoPrice,
                                ],
                                [
                                    'number' => 4,
                                    'total' => $promoPrice,
                                ],
                                [
                                    'number' => 5,
                                    'total' => $promoPrice,
                                ],
                                [
                                    'number' => 6,
                                    'total' => $promoPrice,
                                ],
                                [
                                    'number' => 7,
                                    'total' => $promoPrice,
                                ],
                                [
                                    'number' => 8,
                                    'total' => $promoPrice,
                                ],
                                [
                                    'number' => 9,
                                    'total' => $promoPrice,
                                ],
                                [
                                    'number' => 10,
                                    'total' => $promoPrice,
                                ],
                                [
                                    'number' => 11,
                                    'total' => $promoPrice,
                                ],
                                [
                                    'number' => 12,
                                    'total' => $promoPrice,
                                ],
                            ],

                            'statement_descriptor' => 'CodanteIO',
                        ],
                    ],
                ],
            ],
        ]);

        $pagarmeOrder = $response->json();

        // add user to subscription
        $subscription = $user->subscribeToPlan(
            $plan->id,
            $pagarmeOrder['id'],
            'purchase',
            'pending',
            null,
            null,
            $pagarmeOrder['amount']
        );

        return [
            'checkoutLink' => $pagarmeOrder['checkouts'][0]['payment_url'],
            'pagarmeOrderID' => $pagarmeOrder['id'],
            'subscription' => new SubscriptionResource($subscription),
        ];
    }

    // Esse é o novo checkout link do Pagarme.
    public function createCheckoutLinkV2(CreateCheckoutLinkV2Request $request)
    {

        $environment = config('app.env');
        $endpoint = 'https://api.pagar.me/core/v5/paymentlinks';

        if ($environment != 'production') {
            $endpoint = 'https://sdx-api.pagar.me/core/v5/paymentlinks';
        }

        // get with plan_slug:

        $payload = $this->buildCheckoutLinkV2Payload($request);

        $response = Http::withBasicAuth(
            config('services.pagarme.api_key'),
            ''
        )->post($endpoint, $payload);

        return $response->json();
    }

    private function buildCheckoutLinkV2Payload(CreateCheckoutLinkV2Request $request)
    {
        $validated = $request->validated();

        $plan = Plan::where('slug', $validated['plan_slug'])->firstOrFail();

        $acceptedPaymentMethods = $validated['accepted_payment_methods'] ?? [
            'credit_card',
            'boleto',
            'pix',
        ];

        $installmentsSetup = [
            'interest_type' => 'simple',
            'max_installments' => 12,
            'amount' => $plan->price_in_cents,
            'interest_rate' => 0,
        ];

        $boletoSettings = [
            'due_in' => 2,
        ];

        $pixSettings = [
            'expires_in' => 4,
        ];

        $items = [
            [
                'name' => $plan->name,
                'amount' => $plan->price_in_cents,
                'description' => $validated['plan_description'] ?? '',
                'default_quantity' => 1,
            ],
        ];

        return [
            'name' => $plan->slug,
            'is_building' => false,
            'payment_settings' => [
                'accepted_payment_methods' => $acceptedPaymentMethods,
                'credit_card_settings' => [
                    'installments_setup' => $installmentsSetup,
                    'operation_type' => 'auth_and_capture',
                ],
                'boleto_settings' => $boletoSettings,
                'pix_settings' => $pixSettings,
            ],
            'cart_settings' => [
                'items' => $items,
            ],
            'type' => 'order',
        ];
    }

    // Função para retornar na página de sucesso.
    // Vamos pegar também o status dela para saber se foi pago ou não.
    public function getSubscriptionByPagarmeOrderId($pagarmeOrderID)
    {
        // vamos checar se o usuário está autenticado e é o owner da subscription

        $user = Auth::user();
        $subscription = $user
            ->subscriptions()
            ->where('provider_id', $pagarmeOrderID)
            ->first();

        if (! $subscription || $subscription->user_id !== $user->id) {
            return response()->json(['message' => 'Não autorizado'], 401);
        }

        $endpoint = "https://api.pagar.me/core/v5/orders/{$pagarmeOrderID}";

        $response = Http::withBasicAuth(
            config('services.pagarme.api_key'),
            ''
        )->get($endpoint);

        $responseData = $response->json();

        // vamos fazer update do status da subscription
        if ($responseData['status'] === 'paid') {
            $subscription->changeStatus('active');

            Mail::to($user->email)->send(
                new PaymentConfirmed($user, $subscription)
            );
        }

        return new SubscriptionResource($subscription);
    }
}
