<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PagarmeService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.pagar.me/core/v5';

    public function __construct()
    {
        $this->apiKey = config('services.pagarme.api_key');
    }

    private function buildPaymentData($paymentInfo)
    {
        switch ($paymentInfo['paymentMethod']) {
            case 'credit_card':
                return [
                        'card_token' => $paymentInfo['cardToken'],
                        'installments' => $paymentInfo['installments'],
                        'card_name' => $paymentInfo['cardName'],
                        'card' => [
                            'billing_address' => [
                                "country" => "BR",
                                "state" => "MG",
                                "city" => "Belo Horizonte",
                                "zip_code" => "30130000",
                                "line_1" => "Rua Teste",
                                "line_2" => "Apto 123"
                            ]
                        ]
                    ];
            case 'pix':
                return [
                    'expires_in' => 60 * 10,
                ];
        }
    }

    private function buildOrderData($customer, $plan, $paymentInfo)
    {
        
        return [
            'customer' => [
                'name' => $customer->name,
                'email' => $customer->email,
                'code' => $customer->id,
                'phones' => [
                    'mobile_phone' => [
                        'country_code' => '55',
                        'area_code' => '11',
                        'number' => $paymentInfo['phone'] ?? $customer->phone ?? '999999999',
                    ],
                ],
                'document' => $paymentInfo['document'],
                'type' => strlen($paymentInfo['document']) === 11 ? 'individual' : 'company',
            ],
            'items' => [
                [
                    'name' => $plan->name,
                    'amount' => $plan->price_in_cents - 10000,
                    'quantity' => 1,
                    'description' => $plan->name,
                    'code' => $plan->id,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => $paymentInfo['paymentMethod'],
                    $paymentInfo['paymentMethod'] => $this->buildPaymentData($paymentInfo)
                ]
            ],
        ];
    }

   public function createOrder($customer, $plan, $paymentInfo)
   {
    
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':'),
            'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/orders", $this->buildOrderData($customer, $plan, $paymentInfo));

        return $response->json();
    }
}
