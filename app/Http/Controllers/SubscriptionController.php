<?php

namespace App\Http\Controllers;

use App\Http\Resources\SubscriptionResource;
use App\Mail\PaymentConfirmed;
use App\Mail\UserJoinedChallenge;
use App\Mail\UserSubscribedToPlan;
use App\Models\Plan;
use App\Notifications\Discord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use PagarMe\Client as PagarMe;
use PagarMe\Exceptions\PagarMeException;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:sanctum");
    }

    public function subscribe(Request $request)
    {
        $requestData = $request->validate([
            "pagarmeToken" => "required",
            "paymentMethod" => "required",
        ]);

        $user = $request->user();
        $plan = Plan::findOrFail(1);

        $pagarme = new PagarMe(config("services.pagarme.api_key"));

        try {
            $transaction = $pagarme->transactions()->capture([
                "id" => $requestData["pagarmeToken"],
                "amount" => $plan->price_in_cents,
            ]);
        } catch (PagarMeException $e) {
            $message = "Erro Pagarme Transaction - " . $e->getMessage();
            $message .= " - User - " . $user->email;

            new Discord($message, "notificacoes");
            abort(500, $e->getMessage());
        } catch (\Exception $e) {
            $message =
                "Erro Pagarme: " . $e->getCode() . " - " . $e->getMessage();
            $message .= " - User - " . $user->email;

            new Discord($message, "notificacoes");
            abort(
                500,
                "Houve um erro com o provedor de pagamentos 😥. Entre em contato com a nossa equipe."
            );
        }

        // o padrão do status é pending. Mas se vier como paid, vamos marcar já para ativar o plano.
        $subscriptionStatus = "pending";
        if ($transaction->status === "paid") {
            $subscriptionStatus = "active";
        }

        $subscription = $user->subscribeToPlan(
            1,
            $transaction->id,
            "purchase",
            $subscriptionStatus,
            $transaction->payment_method,
            $transaction->boleto_url ?? null
        );

        // Envia email de confirmação de Assinatura
        Mail::to($user->email)->send(
            new UserSubscribedToPlan($user, $subscription)
        );

        if ($subscriptionStatus === "active") {
            $user->upgradeUserToPro();

            // Envia email de confirmação de pagamento.
            Mail::to($user->email)->send(
                new PaymentConfirmed($user, $subscription)
            );
        }

        return new SubscriptionResource($subscription);
    }

    public function showSubscription()
    {
        $subscriptions = Auth::user()
            ->subscriptions()
            ->orderBy("created_at", "desc")
            ->get();

        // if there is no subscription, return null
        if ($subscriptions->count() === 0) {
            return null;
        }

        // if there is only one subscription, return this subscription
        if ($subscriptions->count() === 1) {
            return new SubscriptionResource($subscriptions->first());
        }

        // if there is more than one subscription, return the active one
        $activeSubscription = $subscriptions
            ->filter(function ($subscription) {
                return $subscription->status === "active";
            })
            ->first();

        if ($activeSubscription) {
            return new SubscriptionResource($activeSubscription);
        }

        // if there is no active subscription, return the most recent
        return new SubscriptionResource($subscriptions->first());
    }
}
