<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayPalPaymentService
{
    public function isConfigured(): bool
    {
        return filled(config('paypal.client_id'))
            && filled(config('paypal.client_secret'))
            && ! config('paypal.mock_mode');
    }

    public function createCheckoutOrder(Payment $payment, string $description): array
    {
        if (! $this->isConfigured()) {
            return $this->createMockCheckout($payment);
        }

        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->post($this->baseUrl().'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $payment->payment_number,
                    'description' => $description,
                    'custom_id' => (string) $payment->id,
                    'amount' => [
                        'currency_code' => config('paypal.currency', 'USD'),
                        'value' => number_format((float) $payment->amount, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'return_url' => route('payments.paypal.success', ['payment' => $payment->id]),
                    'cancel_url' => route('payments.paypal.cancel', ['payment' => $payment->id]),
                    'brand_name' => config('app.name', 'Marketi'),
                    'user_action' => 'PAY_NOW',
                ],
            ]);

        if (! $response->successful()) {
            Log::error('PayPal order creation failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            throw new RuntimeException('Failed to create PayPal payment link');
        }

        $data = $response->json();
        $approveUrl = collect($data['links'] ?? [])->firstWhere('rel', 'approve')['href'] ?? null;

        if (! $approveUrl) {
            throw new RuntimeException('PayPal approval URL not found');
        }

        return [
            'order_id' => $data['id'],
            'payment_url' => $approveUrl,
            'status' => $data['status'] ?? 'CREATED',
            'gateway_response' => $data,
        ];
    }

    public function captureOrder(string $paypalOrderId): array
    {
        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->post($this->baseUrl().'/v2/checkout/orders/'.$paypalOrderId.'/capture');

        if (! $response->successful()) {
            Log::error('PayPal capture failed', [
                'order_id' => $paypalOrderId,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            throw new RuntimeException('Failed to capture PayPal payment');
        }

        return $response->json();
    }

    public function fetchOrder(string $paypalOrderId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->get($this->baseUrl().'/v2/checkout/orders/'.$paypalOrderId);

        return $response->successful() ? $response->json() : null;
    }

    private function accessToken(): string
    {
        return Cache::remember('paypal_access_token', 3000, function () {
            $response = Http::withBasicAuth(
                config('paypal.client_id'),
                config('paypal.client_secret')
            )
                ->asForm()
                ->post($this->baseUrl().'/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if (! $response->successful()) {
                throw new RuntimeException('Failed to authenticate with PayPal');
            }

            return $response->json('access_token');
        });
    }

    private function baseUrl(): string
    {
        return config('paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    private function createMockCheckout(Payment $payment): array
    {
        return [
            'order_id' => 'mock_'.uniqid(),
            'payment_url' => url("/api/v1/payments/mock/{$payment->id}/checkout"),
            'status' => 'CREATED',
            'gateway_response' => [
                'mock' => true,
                'message' => 'Mock PayPal mode — POST /api/v1/payments/mock/{id}/complete to simulate payment',
            ],
        ];
    }
}
