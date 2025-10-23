<?php

namespace App\Http\Controllers;

use App\Http\Resources\SubscriptionResource;
use App\Mail\PaymentConfirmed;
use App\Models\Coupon;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mail;

class PagarmeController extends Controller
{
    private const CODANDO_COM_IA_PRICE_IN_CENTS = 58800;
    private const CODANDO_COM_IA_PRODUCT_SLUG = 'curso-ao-vivo-codando-com-ia-v1';

    public function __construct()
    {
        $this->middleware('auth:sanctum')->only([
            'createOrderAndGetCheckoutLink',
            'getSubscriptionByPagarmeOrderId',
        ]);
    }

    public function createCodandoComIaCheckout(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:30',
            'tag' => 'nullable|string|max:255',
        ]);

        $rawPhone = preg_replace('/\D/', '', $validated['phone']);

        if (Str::startsWith($rawPhone, '55') && strlen($rawPhone) > 11) {
            $rawPhone = substr($rawPhone, 2);
        }

        $phonesPayload = null;

        if (strlen($rawPhone) >= 10) {
            $areaCode = substr($rawPhone, 0, 2);
            $number = substr($rawPhone, 2);

            $phonesPayload = [
                'country_code' => '55',
                'area_code' => $areaCode,
                'number' => $number,
            ];
        }

        $customerPayload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'type' => 'individual',
            'metadata' => [
                'product_slug' => self::CODANDO_COM_IA_PRODUCT_SLUG,
                'lead_tag' => $validated['tag'] ?? null,
            ],
        ];

        if ($phonesPayload) {
            $customerPayload['phones'] = [
                'mobile_phone' => $phonesPayload,
            ];
        }

        $installments = collect(range(1, 12))->map(function ($number) {
            return [
                'number' => $number,
                'total' => self::CODANDO_COM_IA_PRICE_IN_CENTS,
            ];
        })->values()->all();

        $checkoutPayload = [
            'code' => 'codando-com-ia-'.Str::uuid()->toString(),
            'customer' => $customerPayload,
            'items' => [
                [
                    'id' => 'codando-com-ia',
                    'amount' => self::CODANDO_COM_IA_PRICE_IN_CENTS,
                    'description' => 'Curso Codando com IA - Compra Única',
                    'quantity' => 1,
                    'code' => self::CODANDO_COM_IA_PRODUCT_SLUG,
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
                        'success_url' => config('app.frontend_url').'/curso-ao-vivo/codando-com-ia/sucesso',
                        'pix' => [
                            'expires_in' => 86400,
                        ],
                        'boleto' => [
                            'due_at' => Carbon::now()
                                ->addDays(3)
                                ->toIso8601String(),
                        ],
                        'credit_card' => [
                            'installments' => $installments,
                            'operation_type' => 'auth_and_capture',
                        ],
                    ],
                ],
            ],
            'metadata' => [
                'product_slug' => self::CODANDO_COM_IA_PRODUCT_SLUG,
                'lead_phone' => $validated['phone'],
                'lead_tag' => $validated['tag'] ?? null,
                'lead_email' => $validated['email'],
                'lead_name' => $validated['name'],
                'lead_phone_digits' => $phonesPayload
                    ? $phonesPayload['area_code'].$phonesPayload['number']
                    : null,
            ],
        ];

        $response = Http::withBasicAuth(
            config('services.pagarme.api_key'),
            ''
        )->post('https://api.pagar.me/core/v5/orders', $checkoutPayload);

        if ($response->failed()) {
            $status = $response->status();
            $errorPayload = $response->json();

            logger()->error('Pagarme order checkout creation failed', [
                'status' => $status,
                'payload' => $checkoutPayload,
                'response' => $errorPayload,
                'response_body' => $response->body(),
            ]);

            return response()->json([
                'message' => 'Não foi possível iniciar o checkout no momento.',
                'details' => $errorPayload,
            ], $status > 0 ? $status : 400);
        }

        $order = $response->json();

        return [
            'checkoutLink' => $order['checkouts'][0]['payment_url'] ?? null,
            'pagarmeOrderID' => $order['id'] ?? null,
            'amount' => $order['amount'] ?? self::CODANDO_COM_IA_PRICE_IN_CENTS,
            'status' => $order['status'] ?? null,
        ];
    }

    public function getCodandoComIaOrderStatus(string $orderId)
    {
        $endpoint = "https://api.pagar.me/core/v5/orders/{$orderId}";

        $response = Http::withBasicAuth(
            config('services.pagarme.api_key'),
            ''
        )->get($endpoint);

        if ($response->failed()) {
            $status = $response->status();

            return response()->json([
                'message' => 'Não foi possível recuperar o status do pedido.',
            ], $status > 0 ? $status : 400);
        }

        $order = $response->json();
        $productSlug = $order['metadata']['product_slug'] ?? null;

        if ($productSlug !== self::CODANDO_COM_IA_PRODUCT_SLUG) {
            return response()->json(['message' => 'Pedido não encontrado'], 404);
        }

        return [
            'id' => $order['id'] ?? null,
            'status' => $order['status'] ?? null,
            'amount' => $order['amount'] ?? null,
            'charges' => $order['charges'] ?? [],
            'customer' => [
                'name' => $order['customer']['name'] ?? null,
                'email' => $order['customer']['email'] ?? null,
            ],
        ];
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
        $coupon = (new Coupon())->getValidCoupon($couponCode, $plan_id);

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
                        'success_url' => config('app.frontend_url').'/assine/sucesso',
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
