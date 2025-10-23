<?php

namespace App\Http\Controllers;

use App\Events\PurchaseCompleted;
use App\Events\PurchaseStarted;
use App\Mail\PaymentConfirmed;
use App\Mail\SubscriptionCanceled;
use App\Mail\UserSubscribedToPlan;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Discord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Mail;
use Symfony\Component\HttpFoundation\Response;

class PagarmeWebhooks
{
    public function handleWebhook(Request $request)
    {
        $eventType = $request->post('type');
        $pagarmeOrderId = $request->post('data')['id'];
        $couponCode =
            $request->post('data')['customer']['metadata']['coupon_code'] ??
            null;

        Discord::sendMessage("Entrando nos Webhooks... (Evento $eventType) - Coupon: $couponCode", 'notificacoes-compras');

        // Se não for uma transaction, não vamos fazer nada.
        if (! Str::of($eventType)->contains('order.')) {
            Discord::sendMessage('Erro, evento não trackeado', 'notificacoes-compras');

            return new Response;
        }

        // Se não encontrarmos uma subscription com o provider_id, não vamos fazer nada.
        $subscription = Subscription::where(
            'provider_id',
            $pagarmeOrderId
        )->first();

        if (! $subscription) {
            // Verificar se é o produto "Codando com IA - Nesse caso vamos criar a subscription e o usuário"
            $productDescription = $request->post('data')['items'][0]['description'] ?? null;

            if (! Str::contains($productDescription, 'Codando com IA')) {
                Discord::sendMessage("Erro, não há subscription com o id {$pagarmeOrderId}", 'notificacoes-compras');

                return new Response;
            }

            // Criar/buscar usuário
            $user = $this->findOrCreateUserFromWebhook($request->post('data'));

            if (! $user) {
                Discord::sendMessage("Erro ao criar/buscar usuário para order {$pagarmeOrderId}", 'notificacoes-compras');

                return new Response;
            }

            // Criar subscription
            $subscription = $this->findOrCreateSubscriptionFromWebhook(
                $pagarmeOrderId,
                $user,
                $request->post('data')
            );

            if (! $subscription) {
                Discord::sendMessage("Erro ao criar subscription para order {$pagarmeOrderId} - Plano não encontrado", 'notificacoes-compras');

                return new Response;
            }

            Discord::sendMessage("✨ Subscription criada via webhook para {$user->email}", 'notificacoes-compras');
        } else {
            $user = User::find($subscription->user_id);
        }

        if (! $user) {
            Discord::sendMessage("Erro, usuário não encontrado para subscription {$pagarmeOrderId}", 'notificacoes-compras');

            return new Response;
        }

        // Vamos chamar os métodos de acordo com o status da transação.

        // Se o evento é order.closed, vamos adicionar os dados do pagamento (que até então não temos)
        if ($eventType === 'order.closed') {
            $this->handleOrderClosed($request, $subscription, $user);
        }

        if ($eventType === 'order.created') {
            event(new PurchaseStarted($user, $subscription));
        }

        $newStatus = Str::of($request->post('data')['status'])->lower();

        switch ($newStatus) {
            case 'paid':
                // Ativar o plano
                $this->handlePaid($request, $subscription, $user, $couponCode);
                break;
            case 'pending':
                // Cancelar o plano
                $this->handlePending($request, $subscription, $user);
                break;
            case 'failed':
            case 'canceled':
                $this->handleCanceled($request, $subscription, $user);
                break;
            default:
                break;
        }

        return new Response;
    }

    public function handlePaid(
        $request,
        Subscription $subscription,
        User $user,
        ?string $couponCode = null
    ) {
        Discord::sendMessage('chamando handlePaid', 'notificacoes-compras');
        // se status anterior é ativo, não faz nada.
        if ($subscription->status === 'active') {
            return;
        }

        // Verificar se é o produto "Codando com IA"
        $isCodandoComIA = $subscription->plan && Str::contains($subscription->plan->name, 'Codando com IA');

        if ($isCodandoComIA) {
            // Para o produto "Codando com IA", apenas atualiza o status sem promover a PRO
            $subscription->status = 'active';
            $subscription->save();

            Discord::sendMessage('Pagarme: Pagamento confirmado para Codando com IA (sem upgrade PRO)', 'notificacoes-compras');

            return;
        }

        // Muda status para ativo
        $subscription->changeStatus('active');

        // Muda status do User
        $user->upgradeUserToPro();

        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            $coupon->markAsUsed();
        }

        // Manda email de pagamento.
        Mail::to($user->email)->send(
            new PaymentConfirmed($user, $subscription)
        );

        event(new PurchaseCompleted($user, $subscription));

        Discord::sendMessage('Pagarme: O novo status é Pago', 'notificacoes-compras');
    }

    public function handleCanceled(
        $request,
        Subscription $subscription,
        User $user
    ) {
        Discord::sendMessage('chamando handle canceled', 'notificacoes-compras');

        // Verificar se é o produto "Codando com IA"
        $isCodandoComIA = $subscription->plan && Str::contains($subscription->plan->name, 'Codando com IA');

        if ($isCodandoComIA) {
            // Para o produto "Codando com IA", apenas atualiza o status
            $subscription->status = 'canceled';
            $subscription->save();

            Discord::sendMessage('Pagarme: Assinatura cancelada para Codando com IA', 'notificacoes-compras');

            return;
        }

        // Muda status para refunded
        $subscription->changeStatus('canceled');

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
        Discord::sendMessage('chamando handlePending', 'notificacoes-compras');

        $this->savePhoneNumber($request, $user);

        // manda no discord dados do usuário
        Discord::sendMessage('Usuário: ' . $user->name . ' - Email: ' . $user->email, 'notificacoes-compras');

        $this->sendPhoneNumberDiscordNotification($user);
        // Muda status para chargedback
        $subscription->changeStatus('pending');

        // Muda status do User
        $user->downgradeUserFromPro();
    }

    // Essa função serve para completarmos dados de pagamento (por exemplo, meio de pagamento e dados do boleto.)
    protected function handleOrderClosed($request, $subscription, $user)
    {
        // save mobile phone if it's not saved

        $this->savePhoneNumber($request, $user);
        $this->sendPhoneNumberDiscordNotification($user);

        Discord::sendMessage('chamando handleOrderClosed', 'notificacoes-compras');
        Discord::sendMessage('🎉 Nova assinatura: ' . $user->name, 'notificacoes-compras');

        $paymentMethod = $request->post('data')['charges'][0]['payment_method'];
        $boletoBarcode = null;
        $boletoUrl = null;

        if ($paymentMethod === 'boleto') {
            $boletoBarcode =
                $request->post('data')['charges'][0]['last_transaction']['line'] ?? null;

            $boletoUrl =
                $request->post('data')['charges'][0]['last_transaction']['url'] ?? null;
        }

        if ($paymentMethod === 'pix') {
            $boletoBarcode =
                $request->post('data')['charges'][0]['last_transaction']['qr_code'] ?? null;

            $boletoUrl =
                $request->post('data')['charges'][0]['last_transaction']['qr_code_url'] ?? null;
        }

        $subscription->update([
            'payment_method' => $paymentMethod,
            'boleto_url' => $boletoUrl,
            'boleto_barcode' => "$boletoBarcode",
        ]);

        $planName = $subscription->plan->name ?? 'Indefinido';
        $planPrice = $subscription->plan->price ?? 0;

        // Verificar se é o produto "Codando com IA" para não enviar email
        $isCodandoComIA = $subscription->plan && Str::contains($subscription->plan->name, 'Codando com IA');

        if (! $isCodandoComIA) {
            // Nesse momento vamos agradecer por se inscrever.
            Mail::to($user->email)->send(
                new UserSubscribedToPlan($user, $subscription)
            );
        }

        Discord::sendMessage('🔒 Assinatura: ' . $planName . ' - ' . $planPrice, 'notificacoes-compras');
    }

    private function savePhoneNumber($request, $user)
    {
        if ($user->mobile_phone) {
            return;
        }

        $data = $request->post('data') ?? [];
        $mobilePhone = data_get($data, 'customer.phones.mobile_phone');

        $candidate = '';

        // Try to extract from customer.phones.mobile_phone
        if (is_array($mobilePhone)) {
            $candidate = ($mobilePhone['country_code'] ?? '') .
                ($mobilePhone['area_code'] ?? '') .
                ($mobilePhone['number'] ?? '');
        }

        $candidate = preg_replace('/\D/', '', $candidate ?? '');

        // Fallback to metadata fields if not found
        if ($candidate === '') {
            $candidate = preg_replace(
                '/\D/',
                '',
                (string) data_get($data, 'metadata.lead_phone_digits', '')
            );
        }

        if ($candidate === '') {
            $candidate = preg_replace(
                '/\D/',
                '',
                (string) data_get($data, 'metadata.lead_phone', '')
            );
        }

        if ($candidate === '') {
            return;
        }

        // Remove country code if present
        if (Str::startsWith($candidate, '55') && strlen($candidate) > 11) {
            $candidate = substr($candidate, 2);
        }

        if ($candidate !== '') {
            $user->mobile_phone = $candidate;
            $user->save();
        }
    }

    private function sendPhoneNumberDiscordNotification($user)
    {
        if (! $user->mobile_phone) {
            Discord::sendMessage('📵 Usuário não tem telefone', 'notificacoes-compras');

            return;
        }

        Discord::sendMessage('☎️ Telefone: ' . $user->mobile_phone, 'notificacoes-compras');
        Discord::sendMessage('☎️ Whatsapp Click to Chat: <https://wa.me/' .
            $user->mobile_phone .
            '>', 'notificacoes-compras');
    }

    private function findOrCreateUserFromWebhook(array $data): ?User
    {
        $email = $data['customer']['email'] ?? null;

        if (! $email) {
            return null;
        }

        // Buscar usuário existente
        $user = User::where('email', $email)->first();

        // Extrair telefone
        $mobilePhone = data_get($data, 'customer.phones.mobile_phone');
        $candidate = '';

        if (is_array($mobilePhone)) {
            $candidate = ($mobilePhone['country_code'] ?? '') .
                ($mobilePhone['area_code'] ?? '') .
                ($mobilePhone['number'] ?? '');
        }

        $candidate = preg_replace('/\D/', '', $candidate ?? '');

        // Fallback para metadata
        if ($candidate === '') {
            $candidate = preg_replace(
                '/\D/',
                '',
                (string) data_get($data, 'metadata.lead_phone_digits', '')
            );
        }

        if ($candidate === '') {
            $candidate = preg_replace(
                '/\D/',
                '',
                (string) data_get($data, 'metadata.lead_phone', '')
            );
        }

        // Remover código do país se presente
        if (Str::startsWith($candidate, '55') && strlen($candidate) > 11) {
            $candidate = substr($candidate, 2);
        }

        $phoneNumber = $candidate !== '' ? $candidate : null;

        if ($user) {
            // Atualizar telefone se estiver vazio
            if (! $user->mobile_phone && $phoneNumber) {
                $user->mobile_phone = $phoneNumber;
                $user->save();
            }

            return $user;
        }

        // Criar novo usuário
        $user = new User;
        $user->name = $data['customer']['name'] ?? 'Usuário';
        $user->email = $email;
        $user->password = Hash::make(Str::random(32));
        $user->mobile_phone = $phoneNumber;
        $user->email_verified_at = null;
        $user->save();

        return $user;
    }

    private function findOrCreateSubscriptionFromWebhook(
        string $pagarmeOrderId,
        User $user,
        array $data
    ): ?Subscription {
        // Buscar subscription existente
        $subscription = Subscription::where('provider_id', $pagarmeOrderId)->first();

        if ($subscription) {
            return $subscription;
        }

        // Extrair nome do produto
        $productName = $data['items'][0]['description'] ?? null;

        if (! $productName) {
            return null;
        }

        // Buscar plano pelo nome
        $plan = Plan::where('name', $productName)->first();

        if (! $plan) {
            return null;
        }

        // Criar subscription
        $subscription = $user->subscribeToPlan(
            $plan->id,
            $pagarmeOrderId,
            'purchase',
            'pending',
            null,
            null,
            $data['amount'] ?? null
        );

        return $subscription;
    }
}
