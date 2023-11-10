<?php

namespace App\Http\Controllers;

use App\Http\Resources\SubscriptionResource;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class PagarmeController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:sanctum");
    }

    public function createOrderAndGetCheckoutLink()
    {
        $user = Auth::user();
        $plan = Plan::find(1);

        $endpoint = "https://api.pagar.me/core/v5/orders";
        // to get pagarme checkout link we need to create an order:

        $response = Http::withBasicAuth(
            config("services.pagarme.api_key"),
            ""
        )->post($endpoint, [
            "customer" => [
                "name" => $user->name,
                "email" => $user->email,
                "code" => $user->id,
            ],
            "items" => [
                [
                    "id" => "1",
                    "amount" => $plan->price_in_cents,
                    "description" => "Codante Vitalício - PRO",
                    "quantity" => 1,
                    "code" => $plan->id,
                ],
            ],
            "payments" => [
                [
                    "payment_method" => "checkout",
                    "checkout" => [
                        "customer_editable" => true,
                        "accepted_payment_methods" => [
                            "credit_card",
                            "boleto",
                            "pix",
                        ],
                        "success_url" =>
                            config("app.frontend_url") . "/assine/sucesso",
                        "pix" => [
                            "expires_in" => 86400,
                        ],
                        "boleto" => [
                            "due_at" => Carbon::now()
                                ->addDays(3)
                                ->toIso8601String(),
                        ],
                        "credit_card" => [
                            "installments" => [
                                [
                                    "number" => 1,
                                    "total" => $plan->price_in_cents,
                                ],
                                [
                                    "number" => 2,
                                    "total" => $plan->price_in_cents,
                                ],
                                [
                                    "number" => 3,
                                    "total" => $plan->price_in_cents,
                                ],
                                [
                                    "number" => 4,
                                    "total" => $plan->price_in_cents,
                                ],
                                [
                                    "number" => 5,
                                    "total" => $plan->price_in_cents,
                                ],
                                [
                                    "number" => 6,
                                    "total" => $plan->price_in_cents,
                                ],
                                [
                                    "number" => 7,
                                    "total" => $plan->price_in_cents,
                                ],
                                [
                                    "number" => 8,
                                    "total" => $plan->price_in_cents,
                                ],
                                [
                                    "number" => 9,
                                    "total" => $plan->price_in_cents,
                                ],
                                [
                                    "number" => 10,
                                    "total" => $plan->price_in_cents,
                                ],
                                [
                                    "number" => 11,
                                    "total" => $plan->price_in_cents,
                                ],
                                [
                                    "number" => 12,
                                    "total" => $plan->price_in_cents,
                                ],
                            ],

                            "statement_descriptor" => "CodanteIO",
                        ],
                    ],
                ],
            ],
        ]);

        $pagarmeOrder = $response->json();

        // add user to subscription
        $subscription = $user->subscribeToPlan(
            $plan->id,
            $pagarmeOrder["id"],
            "purchase",
            "pending",
            null,
            null,
            $pagarmeOrder["amount"]
        );

        return [
            "checkoutLink" => $pagarmeOrder["checkouts"][0]["payment_url"],
            "pagarmeOrderID" => $pagarmeOrder["id"],
            "subscription" => new SubscriptionResource($subscription),
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
            ->where("provider_id", $pagarmeOrderID)
            ->first();

        if (!$subscription || $subscription->user_id !== $user->id) {
            return response()->json(["message" => "Não autorizado"], 401);
        }

        $endpoint = "https://api.pagar.me/core/v5/orders/{$pagarmeOrderID}";

        $response = Http::withBasicAuth(
            config("services.pagarme.api_key"),
            ""
        )->get($endpoint);

        $responseData = $response->json();

        // vamos fazer update do status da subscription
        if ($responseData["status"] === "paid") {
            $subscription->changeStatus("active");
        }

        return new SubscriptionResource($subscription);
    }
}
