<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class BakongService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected ?string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.bakong_gateway.url'), '/');
        $this->apiKey = config('services.bakong_gateway.api_key');
        $this->token = config('services.bakong_gateway.token');
    }

    protected function request(string $method, string $endpoint, array $data = [])
    {
        try {
            $http = Http::withHeaders([
                'X-API-KEY'    => $this->apiKey,
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ]);

            if (!empty($this->token)) {
                $http = $http->withToken($this->token);
            }

            $url = $this->baseUrl . $endpoint;

            $response = match (strtolower($method)) {
                'get'    => $http->get($url, $data),
                'post'   => $http->post($url, $data),
                'put'    => $http->put($url, $data),
                'patch'  => $http->patch($url, $data),
                'delete' => $http->delete($url, $data),
                default  => throw new \Exception("Unsupported HTTP method: {$method}"),
            };

            if (!$response instanceof \Illuminate\Http\Client\Response) {
                throw new \Exception("Unexpected response type");
            }

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status'  => $response->status(),
                    'data'    => $response->json(),
                ];
            }

            return [
                'success' => false,
                'status'  => $response->status(),
                'message' => $response->json()['message'] ?? 'Request failed',
                'errors'  => $response->json(),
            ];
        } catch (RequestException $e) {
            return [
                'success' => false,
                'message' => 'HTTP Request Exception',
                'error'   => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Unexpected Exception',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate KHQR for individual payments
     */
    public function generateIndividualKhqr(array $payload)
    {
        return $this->request('post', '/api/v1/khqr/individual', [
            'bakong_account_id'      => $payload['bakong_account_id'] ?? null,
            'merchant_name'          => $payload['merchant_name'] ?? null,
            'account_information'    => $payload['account_information'] ?? null,
            'currency'               => strtolower($payload['currency'] ?? 'khr'),
            'amount'                 => $payload['amount'] ?? null,
            'merchant_city'          => $payload['merchant_city'] ?? 'Phnom Penh',
            'bill_number'            => $payload['bill_number'] ?? null,
            'mobile_number'          => $payload['mobile_number'] ?? null,
            'store_label'            => $payload['store_label'] ?? null,
            'terminal_label'         => $payload['terminal_label'] ?? null,
            'purpose_of_transaction' => $payload['purpose_of_transaction'] ?? 'Payment',
            'expiration_timestamp'   => $payload['expiration_timestamp'] ?? null,
            'merchant_category_code' => $payload['merchant_category_code'] ?? '5999',
        ]);
    }

    /**
     * Generate KHQR for merchant payments
     */
    public function generateMerchantKhqr(array $payload)
    {
        return $this->request('post', '/api/v1/khqr/merchant', [
            'bakong_account_id'       => $payload['bakong_account_id'] ?? null,
            'merchant_id'             => $payload['merchant_id'] ?? null,
            'acquiring_bank'          => $payload['acquiring_bank'] ?? null,
            'currency'                => strtolower($payload['currency'] ?? 'usd'),
            'amount'                  => $payload['amount'] ?? null,
            'merchant_name'           => $payload['merchant_name'] ?? null,
            'merchant_city'           => $payload['merchant_city'] ?? 'Phnom Penh',
            'bill_number'             => $payload['bill_number'] ?? null,
            'mobile_number'           => $payload['mobile_number'] ?? null,
            'store_label'             => $payload['store_label'] ?? null,
            'terminal_label'          => $payload['terminal_label'] ?? null,
            'purpose_of_transaction'  => $payload['purpose_of_transaction'] ?? null,
            'upi_account_information' => $payload['upi_account_information'] ?? null,
            'expiration_timestamp'    => $payload['expiration_timestamp'] ?? null,
            'merchant_category_code'  => $payload['merchant_category_code'] ?? '5999',
        ]);
    }

    /**
     * Generate styled KHQR image
     */
    public function generateKhqrImage(array $payload)
    {
        return $this->request('post', '/api/v1/khqr/generate-image', [
            'qr'              => $payload['qr'] ?? null,
        ]);
    }

    /**
     * Generate Bakong deeplink
     */
    public function generateDeeplink(array $payload)
    {
        return $this->request('post', '/api/v1/khqr/deeplink', [
            'qr_code' => $payload['qr_code'] ?? null,
            'app_icon_url' => $payload['app_icon_url'] ?? null,
            'app_name' => $payload['app_name'] ?? null,
            'app_deep_link_callback' => $payload['app_deep_link_callback'] ?? null,
        ]);
    }
    /**
     * Check transaction by MD5
     */
    public function checkTransactionByMd5(string $md5)
    {
        return $this->request('post', '/api/v1/khqr/check-transaction-by-md5', [
            'md5' => $md5,
        ]);
    }

    /**
     * Check transaction by short hash
     */
    public function checkTransactionByHash(string $hash)
    {
        return $this->request('post', '/api/v1/khqr/check-transaction-by-hash', [
            'hash' => $hash,
        ]);
    }

    /**
     * Check Bakong account
     */
    public function checkBakongAccount(string $bakongAccountId)
    {
        return $this->request('post', '/api/v1/khqr/check-bakong-account', [
            'bakong_account_id' => $bakongAccountId,
        ]);
    }

    /**
     * Check transaction by external reference
     */
    public function checkTransactionByExternalRef(string $externalRef)
    {
        return $this->request('post', '/api/v1/khqr/check-transaction-by-external-ref', [
            'external_ref' => $externalRef,
        ]);
    }
}