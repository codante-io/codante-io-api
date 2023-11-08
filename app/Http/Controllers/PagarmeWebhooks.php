<?php

namespace App\Http\Controllers;

use App\Mail\PaymentConfirmed;
use App\Mail\PaymentRefunded;
use App\Mail\PaymentRefused;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\Discord;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mail;
use Symfony\Component\HttpFoundation\Response;

class PagarmeWebhooks
{
    public function handleWebhook(Request $request)
    {
        new Discord("Entrando nos Webhooks...", "notificacoes-site");
        new Discord(
            "Status do request do Pagarme é $request->current_status...",
            "notificacoes-site"
        );
        // Validando o postback

        // Se não for uma transaction, não vamos fazer nada.
        if ($request->object !== "transaction") {
            new Discord("Erro, não há transaction", "notificacoes-site");
            return new Response();
        }

        // Se não for um evento de status, não vamos fazer nada.
        if ($request->event !== "transaction_status_changed") {
            new Discord("Erro, evento não trackeado", "notificacoes-site");
            return new Response();
        }

        // Se não encontrarmos uma subscription com o provider_id, não vamos fazer nada.
        $subscription = Subscription::where(
            "provider_id",
            $request->post("id")
        )->first();

        if (!$subscription) {
            return new Response();
        }

        // Se não validar a assinatura, não fazemos nada.
        $isValid = $this->validatePostBack($request);
        if (!$isValid) {
            return new Response('{"message": "not valid signature"}', 401);
        }

        $user = User::find($subscription->user_id);

        // Vamos chamar os métodos de acordo com o status da transação.
        $newStatus = $request->current_status;

        switch ($newStatus) {
            case "paid":
                // Ativar o plano
                $this->handlePaid($request, $subscription, $user);
                break;
            case "pending_refund":
            case "refunded":
                // Cancelar o plano
                $this->handleRefunded($request, $subscription, $user);
                break;
            case "refused":
                $this->handleRefused($request, $subscription, $user);
                break;
            case "chargedback":
                $this->handleChargeback($request, $subscription, $user);
                break;
            case "waiting_payment":
                break;
            default:
                break;
        }

        return new Response();
    }

    public function handlePaid($request, Subscription $subscription, User $user)
    {
        new Discord("chamando handlePaid", "notificacoes-site");

        // Muda status para ativo
        $subscription->changeStatus("active");

        // Muda status do User
        $user->upgradeUserToPro();

        // Manda email de pagamento.
        Mail::to($user->email)->queue(
            new PaymentConfirmed($user, $subscription)
        );

        new Discord(
            "Pagarme: O novo status do ID " . $request->id . " é Pago",
            "notificacoes-site"
        );
    }

    public function handleRefunded(
        $request,
        Subscription $subscription,
        User $user
    ) {
        new Discord("chamando handle refunded", "notificacoes-site");

        // Muda status para refunded
        $subscription->changeStatus("refunded");

        // Manda email de Refund.
        Mail::to($user->email)->queue(
            new PaymentRefunded($user, $subscription)
        );

        // Muda status do User
        $user->downgradeUserFromPro();
    }

    public function handleRefused(
        $request,
        Subscription $subscription,
        User $user
    ) {
        new Discord("chamando handle refused", "notificacoes-site");

        // Muda status para refunded
        $subscription->changeStatus("refused");

        // Manda email de Refund.
        Mail::to($user->email)->queue(new PaymentRefused($user, $subscription));

        // Muda status do User
        $user->downgradeUserFromPro();
    }

    public function handleChargeback(
        $request,
        Subscription $subscription,
        User $user
    ) {
        new Discord("chamando handleChargeback", "notificacoes-site");

        // Muda status para chargedback
        $subscription->changeStatus("chargedback");

        // Muda status do User
        $user->downgradeUserFromPro();
    }

    private function validatePostBack($request)
    {
        // get the signature sent by Pagar.me
        $signature = $request->header("X-Hub-Signature");
        $signature = Str::of($signature)
            ->replace("sha1=", "")
            ->trim()
            ->toString();

        // get the payload sent by Pagar.me
        $payload = $request->getContent();

        // get the public key from Pagar.me
        $publicKey = config("services.pagarme.api_key");

        // generate the signature using the public key and the payload sent by Pagar.me
        $generatedSignature = hash_hmac("sha1", $payload, $publicKey);
        $generatedSignature = Str::of($generatedSignature)
            ->trim()
            ->toString();

        new Discord("Validando request...", "notificacoes-site");

        // check if the signature sent by Pagar.me is the same as the one generated by us
        if ($generatedSignature !== $signature) {
            new Discord(
                "Assinatura da Requisição não validada :/",
                "notificacoes-site"
            );

            return false;
        } else {
            new Discord(
                "Assinatura da Requisição válida!",
                "notificacoes-site"
            );
            return true;
        }
    }
}
