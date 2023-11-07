<?php

namespace App\Http\Controllers\Plans;

use App\Mail\PaymentConfirmed;
use App\Mail\PaymentRefunded;
use App\Models\Pagamento;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\Discord;
use App\Notifications\Slack;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mail;
use Symfony\Component\HttpFoundation\Response;

class PagarmeWebhooks
{
    public function handleWebhook(Request $request)
    {
        $statusPagarme = $request->current_status;
        $method =
            "handle" .
            str_replace(
                " ",
                "",
                Str::title(str_replace("_", " ", $statusPagarme))
            );

        if (method_exists($this, $method)) {
            return $this->{$method}($request);
        }

        return $this->missingMethod($request);
    }

    public function handlePaid($request)
    {
        new Discord("chamando handle paid", "notificacoes-compra");

        $providerId = $request->post("id");
        $subscription = Subscription::where(
            "provider_id",
            $providerId
        )->first();
        $user = User::find($subscription->user_id);

        // Muda status para ativo
        $subscription->changeStatus("active");

        // Manda email de pagamento.
        Mail::to($user->email)->queue(
            new PaymentConfirmed($user, $subscription)
        );

        new Discord(
            "Pagarme: O novo status do ID " . $providerId . " é Pago",
            "notificacoes-site"
        );
    }

    public function handleRefunded($request)
    {
        new Discord("chamando handle refunded", "notificacoes-compra");

        return new Response();

        // $charge_id = $request->post("id");

        // $a = new AddPlanToUsers();
        // $a->changeStatusByProviderId("pme_" . $charge_id, "canceled");

        // $pagamento = Pagamento::where(
        //     "transaction_code",
        //     "pme_" . $charge_id
        // )->first();
        // $pagamento->status_text = "Reembolsado";
        // $pagamento->payment_status = config("planos.payments.status.refunded");
        // $pagamento->save();

        // $user = User::find($pagamento->user_id);
        // \Mail::to($user->email)->queue(new PaymentRefunded($user));

        // new Discord(
        //     "Pagarme: O novo status do ID pme_" .
        //         $charge_id .
        //         " é: " .
        //         $pagamento->status_text,
        //     "notificacoes-compra"
        // );
    }

    public function handleRefused($request)
    {
        new Discord("chamando handle refused", "notificacoes-compra");

        return new Response();

        // $charge_id = $request->post("id");

        // // Se não for achado o plano, significa que nenhuma transação foi feita - então nada mais é necessário fazer no backend.
        // // Vamos retornar então um response 200 para evitar novos postbacks.
        // if (!Subscription::where("provider_id", "pme_" . $charge_id)->first()) {
        //     return new Response();
        // }

        // $a = new AddPlanToUsers();
        // $a->changeStatusByProviderId("pme_" . $charge_id, "canceled");

        // $pagamento = Pagamento::where(
        //     "transaction_code",
        //     "pme_" . $charge_id
        // )->first();
        // $pagamento->status_text = "Falho";
        // $pagamento->payment_status = config("planos.payments.status.fail");
        // $pagamento->save();

        // $user = User::find($pagamento->user_id);
        // \Mail::to($user->email)->queue(new PaymentRefunded($user));

        // new Discord(
        //     "Pagarme: O novo status do ID pme_" .
        //         $charge_id .
        //         " é: " .
        //         $pagamento->status_text,
        //     "notificacoes-compra"
        // );
    }

    public function handleAuthorized($request)
    {
        new Discord("chamando handle authorized", "notificacoes-compra");
        new Discord(
            "O ID - " . $request->post("id") . " foi autorizado.",
            "notificacoes-compra"
        );

        return new Response();
    }

    public function handleWaitingPayment($request)
    {
        new Discord("chamando handle Waiting Payment", "notificacoes-compra");
        new Discord(
            "O ID - " . $request->post("id") . " está aguardando pagamento.",
            "notificacoes-compra"
        );

        return new Response();
    }

    public function handlePendingRefund($request)
    {
        new Discord("chamando handle Pending Refund", "notificacoes-compra");
        new Discord(
            "O ID - " . $request->post("id") . " está pendente de Refund.",
            "notificacoes-compra"
        );

        return new Response();
    }

    public function handleProcessing($request)
    {
        new Discord("chamando handle Processing", "notificacoes-compra");
        new Discord(
            "O ID - " . $request->post("id") . " está processando.",
            "notificacoes-compra"
        );

        return new Response();
    }

    public function missingMethod($request)
    {
        $transactionId = "";

        new Discord(
            "chamando missing method - " .
                $request->event .
                " - " .
                $request->post("id") ??
                "",
            "notificacoes-site"
        );

        return new Response();
    }
}
