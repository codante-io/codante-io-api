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
        $eventType = $request->post("type");

        new Discord("Entrando nos Webhooks...", "notificacoes-site");
        new Discord(
            "Status do request do Pagarme é $request->current_status...",
            "notificacoes-site"
        );

        // Se não for uma transaction, não vamos fazer nada.
        if (!Str::of($eventType)->contains("order.")) {
            new Discord("Erro, evento não trackeado", "notificacoes-site");
            return new Response();
        }

        // Se não encontrarmos uma subscription com o provider_id, não vamos fazer nada.
        $subscription = Subscription::where(
            "provider_id",
            $request->post("id")
        )->first();

        if (!$subscription) {
            new Discord(
                "Erro, não há subscription com o id {$request->post("id")}",
                "notificacoes-site"
            );
            return new Response();
        }

        $user = User::find($subscription->user_id);

        // Vamos chamar os métodos de acordo com o status da transação.

        // Se o evento é order.closed, vamos adicionar os dados do pagamento
        if ($eventType === "order.closed") {
            $this->handleOrderClosed($request, $subscription, $user);
        }

        $newStatus = $request->post("data")["status"];

        switch ($newStatus) {
            case "paid":
                // Ativar o plano
                $this->handlePaid($request, $subscription, $user);
                break;
            case "pending":
                // Cancelar o plano
                $this->handleRefunded($request, $subscription, $user);
                break;
            case "order.updated":
                $this->handleCanceled($request, $subscription, $user);
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
        $apiKey = config("services.pagarme.api_key");

        // generate the signature using the public key and the payload sent by Pagar.me
        $generatedSignature = hash_hmac("sha1", $payload, $apiKey);
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

    protected function handleOrderClosed($request, $subscription, $user)
    {
        new Discord("chamando handleOrderClosed", "notificacoes-site");

        $paymentMethod = $request->post("data")["charges"][0]["payment_method"];
        $boletoBarcode = null;
        $boletoUrl = null;

        if ($paymentMethod === "boleto") {
            $boletoBarcode =
                $request->post("data")["charges"][0]["last_transaction"][
                    "barcode"
                ] ?? null;

            $boletoUrl =
                $request->post("data")["charges"][0]["last_transaction"][
                    "url"
                ] ?? null;
        }

        $subscription->update([
            "payment_method" => $paymentMethod,
            "boleto_url" => $boletoUrl,
            "boleto_barcode" => "$boletoBarcode",
        ]);
    }
}
