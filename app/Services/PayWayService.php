<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayWayService
{
    protected string $baseUrl;
    protected string $merchantId;
    protected string $publicKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('payway.base_url'), '/');
        $this->merchantId = trim((string) config('payway.merchant_id'));
        $this->publicKey = trim((string) config('payway.public_key'));
    }

    private function reqTime(): string
    {
        return now('UTC')->format('YmdHis');
    }

    private function makeHash(string $stringToHash): string
    {
        return base64_encode(hash_hmac('sha512', $stringToHash, $this->publicKey, true));
    }

    public function purchase(array $data): array
    {
        $reqTime = $this->reqTime();

        $tranId = (string) ($data['tran_id'] ?? ('TRX' . now()->format('YmdHis')));

        /*
         * Important:
         * PayWay compares exact string values in hash.
         * So amount in hash and amount in payload must be same.
         */
        $amount = number_format((float) $data['amount'], 2, '.', '');

        $itemsArray = $data['items'] ?? [
            [
                'name' => 'Service Booking',
                'quantity' => 1,
                'price' => (float) $amount,
            ],
        ];

        $items = base64_encode(json_encode($itemsArray, JSON_UNESCAPED_SLASHES));

        $returnUrl = config('payway.return_url')
            ? base64_encode((string) config('payway.return_url'))
            : '';

        $cancelUrl = config('payway.cancel_url') ?? '';

        $continueSuccessUrl = config('payway.success_url') ?? '';

        /*
         * For web checkout, keep empty.
         * For mobile app, send use_default_deeplink=true from request.
         */
        $returnDeeplink = '';

        if (($data['use_default_deeplink'] ?? false) === true) {
            $returnDeeplink = base64_encode(json_encode([
                'ios_scheme' => config('payway.ios_deeplink'),
                'android_scheme' => config('payway.android_deeplink'),
            ], JSON_UNESCAPED_SLASHES));
        }

        $customFields = '';

        if (isset($data['custom_fields']) && is_array($data['custom_fields'])) {
            $customFields = base64_encode(json_encode($data['custom_fields'], JSON_UNESCAPED_SLASHES));
        }

        $payload = [
            'req_time' => $reqTime,
            'merchant_id' => $this->merchantId,
            'tran_id' => $tranId,

            'firstname' => $data['firstname'] ?? '',
            'lastname' => $data['lastname'] ?? '',
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',

            'type' => $data['type'] ?? 'purchase',
            'payment_option' => $data['payment_option'] ?? 'abapay_khqr',

            'items' => $items,
            'shipping' => (string) ($data['shipping'] ?? '0'),
            'amount' => $amount,
            'currency' => $data['currency'] ?? 'USD',

            'return_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
            'skip_success_page' => (string) ($data['skip_success_page'] ?? '1'),
            'continue_success_url' => $continueSuccessUrl,
            'return_deeplink' => $returnDeeplink,

            'custom_fields' => $customFields,
            'return_params' => $data['return_params'] ?? '',

            'view_type' => $data['view_type'] ?? 'hosted_view',
            'payment_gate' => (string) ($data['payment_gate'] ?? '0'),

            'payout' => '',
            'additional_params' => '',
            'lifetime' => (string) ($data['lifetime'] ?? '30'),
            'google_pay_token' => '',
        ];

        /*
         * Correct PayWay Purchase hash order.
         * Do NOT add ctid.
         * Do NOT add pwt.
         */
        $stringToHash =
            $payload['req_time'] .
            $payload['merchant_id'] .
            $payload['tran_id'] .
            $payload['amount'] .
            $payload['items'] .
            $payload['shipping'] .
            $payload['firstname'] .
            $payload['lastname'] .
            $payload['email'] .
            $payload['phone'] .
            $payload['type'] .
            $payload['payment_option'] .
            $payload['return_url'] .
            $payload['cancel_url'] .
            $payload['continue_success_url'] .
            $payload['return_deeplink'] .
            $payload['currency'] .
            $payload['custom_fields'] .
            $payload['return_params'] .
            $payload['payout'] .
            $payload['lifetime'] .
            $payload['additional_params'] .
            $payload['google_pay_token'] .
            $payload['skip_success_page'];

        $payload['hash'] = $this->makeHash($stringToHash);

        Log::info('PAYWAY_PURCHASE_DEBUG', [
            'string_to_hash' => $stringToHash,
            'hash' => $payload['hash'],
            'merchant_id' => $payload['merchant_id'],
            'public_key_length' => strlen($this->publicKey),
            'payload' => $payload,
        ]);

        $response = Http::asMultipart()
            ->post($this->baseUrl . '/api/payment-gateway/v1/payments/purchase', $payload);

        $contentType = $response->header('Content-Type') ?? '';

        if (str_contains($contentType, 'text/html')) {
            return [
                'success' => true,
                'type' => 'html',
                'tran_id' => $tranId,
                'html' => $response->body(),
            ];
        }

        return [
            'success' => $response->successful(),
            'type' => 'json',
            'tran_id' => $tranId,
            'data' => $response->json(),
            'raw' => $response->body(),
            'debug' => [
                'string_to_hash' => $stringToHash,
                'hash' => $payload['hash'],
                'public_key_length' => strlen($this->publicKey),
            ],
        ];
    }

    public function checkTransaction(string $tranId): array
    {
        $reqTime = $this->reqTime();

        $payload = [
            'req_time' => $reqTime,
            'merchant_id' => $this->merchantId,
            'tran_id' => $tranId,
            'hash' => $this->makeHash($reqTime . $this->merchantId . $tranId),
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($this->baseUrl . '/api/payment-gateway/v1/payments/check-transaction-2', $payload);

        return $response->json() ?? [
            'success' => false,
            'raw' => $response->body(),
        ];
    }

    public function closeTransaction(string $tranId): array
    {
        $reqTime = $this->reqTime();

        $payload = [
            'req_time' => $reqTime,
            'merchant_id' => $this->merchantId,
            'tran_id' => $tranId,
            'hash' => $this->makeHash($reqTime . $this->merchantId . $tranId),
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($this->baseUrl . '/api/payment-gateway/v1/payments/close-transaction', $payload);

        return $response->json() ?? [
            'success' => false,
            'raw' => $response->body(),
        ];
    }

    public function requestAccountLinkQr(string $returnParam): array
    {
        $reqTime = $this->reqTime();

        $returnUrl = config('payway.return_url')
            ? base64_encode((string) config('payway.return_url'))
            : '';

        $returnDeeplink = base64_encode(json_encode([
            'ios_scheme' => config('payway.ios_deeplink'),
            'android_scheme' => config('payway.android_deeplink'),
        ], JSON_UNESCAPED_SLASHES));

        $payload = [
            'req_time' => $reqTime,
            'merchant_id' => $this->merchantId,
            'return_param' => $returnParam,
            'return_url' => $returnUrl,
            'return_deeplink' => $returnDeeplink,
            'hash' => $this->makeHash($this->merchantId . $reqTime . $returnDeeplink),
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($this->baseUrl . '/api/aof/request-qr', $payload);

        return $response->json() ?? [
            'success' => false,
            'raw' => $response->body(),
        ];
    }
}