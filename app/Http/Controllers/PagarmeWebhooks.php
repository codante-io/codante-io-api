<?php

namespace App\Http\Controllers;

use App\Events\PurchaseCompleted;
use App\Events\PurchaseStarted;
use App\Mail\PaymentConfirmed;
use App\Mail\SubscriptionCanceled;
use App\Mail\UserSubscribedToPlan;
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
        $pagarmeOrderId = $request->post("data")["id"];

        new Discord(
            "Entrando nos Webhooks... (Evento $eventType)",
            "notificacoes-compras"
        );

        // Se não for uma transaction, não vamos fazer nada.
        if (!Str::of($eventType)->contains("order.")) {
            new Discord("Erro, evento não trackeado", "notificacoes-compras");
            return new Response();
        }

        // Se não encontrarmos uma subscription com o provider_id, não vamos fazer nada.
        $subscription = Subscription::where(
            "provider_id",
            $pagarmeOrderId
        )->first();

        if (!$subscription) {
            new Discord(
                "Erro, não há subscription com o id {$pagarmeOrderId}",
                "notificacoes-compras"
            );
            return new Response();
        }

        $user = User::find($subscription->user_id);

        // Vamos chamar os métodos de acordo com o status da transação.

        // Se o evento é order.closed, vamos adicionar os dados do pagamento (que até então não temos)
        if ($eventType === "order.closed") {
            $this->handleOrderClosed($request, $subscription, $user);
        }

        if ($eventType === "order.created") {
            event(new PurchaseStarted($user, $subscription));
        }

        $newStatus = Str::of($request->post("data")["status"])->lower();

        switch ($newStatus) {
            case "paid":
                // Ativar o plano
                $this->handlePaid($request, $subscription, $user);
                break;
            case "pending":
                // Cancelar o plano
                $this->handlePending($request, $subscription, $user);
                break;
            case "failed":
            case "canceled":
                $this->handleCanceled($request, $subscription, $user);
                break;
            default:
                break;
        }

        return new Response();
    }

    public function handlePaid($request, Subscription $subscription, User $user)
    {
        new Discord("chamando handlePaid", "notificacoes-compras");
        // se status anterior é ativo, não faz nada.
        if ($subscription->status === "active") {
            return;
        }

        // Muda status para ativo
        $subscription->changeStatus("active");

        // Muda status do User
        $user->upgradeUserToPro();

        // Manda email de pagamento.
        Mail::to($user->email)->send(
            new PaymentConfirmed($user, $subscription)
        );

        event(new PurchaseCompleted($user, $subscription));

        new Discord("Pagarme: O novo status é Pago", "notificacoes-compras");
    }

    public function handleCanceled(
        $request,
        Subscription $subscription,
        User $user
    ) {
        new Discord("chamando handle canceled", "notificacoes-compras");

        // Muda status para refunded
        $subscription->changeStatus("canceled");

        // Manda email de Refund.
        Mail::to($user->email)->send(
            new SubscriptionCanceled($user, $subscription)
        );

        // Muda status do User
        $user->downgradeUserFromPro();
    }

    public function handlePending(
        $request,
        Subscription $subscription,
        User $user
    ) {
        new Discord("chamando handlePending", "notificacoes-compras");

        // Muda status para chargedback
        $subscription->changeStatus("pending");

        // Muda status do User
        $user->downgradeUserFromPro();
    }

    // Essa função serve para completarmos dados de pagamento (por exemplo, meio de pagamento e dados do boleto.)
    protected function handleOrderClosed($request, $subscription, $user)
    {
        // save mobile phone if it's not saved

        $this->savePhoneNumber($request, $user);
        $this->sendPhoneNumberDiscordNotification($user);

        new Discord("chamando handleOrderClosed", "notificacoes-compras");
        new Discord(
            "🎉 Nova assinatura: " . $user->name,
            "notificacoes-compras"
        );

        $paymentMethod = $request->post("data")["charges"][0]["payment_method"];
        $boletoBarcode = null;
        $boletoUrl = null;

        if ($paymentMethod === "boleto") {
            $boletoBarcode =
                $request->post("data")["charges"][0]["last_transaction"][
                    "line"
                ] ?? null;

            $boletoUrl =
                $request->post("data")["charges"][0]["last_transaction"][
                    "url"
                ] ?? null;
        }

        if ($paymentMethod === "pix") {
            $boletoBarcode =
                $request->post("data")["charges"][0]["last_transaction"][
                    "qr_code"
                ] ?? null;

            $boletoUrl =
                $request->post("data")["charges"][0]["last_transaction"][
                    "qr_code_url"
                ] ?? null;
        }

        $subscription->update([
            "payment_method" => $paymentMethod,
            "boleto_url" => $boletoUrl,
            "boleto_barcode" => "$boletoBarcode",
        ]);

        // Nesse momento vamos agradecer por se inscrever.
        Mail::to($user->email)->send(
            new UserSubscribedToPlan($user, $subscription)
        );
    }

    private function savePhoneNumber($request, $user)
    {
        $user->mobile_phone =
            $request->post("data")["customer"]["phones"]["mobile_phone"][
                "area_code"
            ] .
            $request->post("data")["customer"]["phones"]["mobile_phone"][
                "number"
            ];
        $user->save();
    }

    private function sendPhoneNumberDiscordNotification($user)
    {
        new Discord("Telefone: " . $user->mobile_phone, "notificacoes-compras");
        new Discord(
            "Whatsapp Click to Chat: https://wa.me/" . $user->mobile_phone,
            "notificacoes-compras"
        );
    }
}
