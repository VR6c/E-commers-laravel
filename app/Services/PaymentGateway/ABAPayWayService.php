<?php

namespace App\Services\PaymentGateway;

use App\Models\PaymentGateway;
use App\Models\PaymentGatewayConfig;
use Illuminate\Support\Facades\Http;

class ABAPayWayService implements PaymentGatewayInterface
{
    protected $merchantId;
    protected $apiKey;
    protected $environment;
    protected $checkoutUrl;

    public function __construct(string $environment = 'sandbox')
    {
        $this->environment = $environment;

        $gateway = PaymentGateway::where('code', 'abapayway')
            ->where('is_active', true)
            ->firstOrFail();

        $configs = PaymentGatewayConfig::where('gateway_id', $gateway->id)
            ->where('environment', $environment)
            ->pluck('key_value', 'key_name');

        $this->merchantId = $configs['merchant_id'] ?? env('PAYWAY_MERCHANT_ID', 'ec476341');
        $this->apiKey = $configs['api_key'] ?? env('PAYWAY_API_KEY', '18e940724353f94ae7b77f4a59cb1fe76bd1e140');

        $this->checkoutUrl = $environment === 'live'
            ? 'https://checkout.payway.com.kh/api/payment-gateway/v1/payments/purchase'
            : 'https://checkout-sandbox.payway.com.kh/api/payment-gateway/v1/payments/purchase';
    }

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function getCheckoutUrl(): string
    {
        return $this->checkoutUrl;
    }

    /**
     * Generate HMAC-SHA512 hash signature for request
     */
    public function generateHash(array $params): string
    {
        $fields = [
            'req_time',
            'merchant_id',
            'tran_id',
            'amount',
            'items',
            'shipping',
            'firstname',
            'lastname',
            'email',
            'phone',
            'type',
            'payment_option',
            'return_url',
            'cancel_url',
            'continue_success_url',
            'return_deeplink',
            'currency',
            'custom_fields',
            'return_params',
            'payout',
            'lifetime',
            'additional_params',
            'google_pay_token',
            'skip_success_page',
        ];

        $b4hash = '';
        foreach ($fields as $field) {
            $b4hash .= isset($params[$field]) ? (string)$params[$field] : '';
        }

        return base64_encode(hash_hmac('sha512', $b4hash, $this->apiKey, true));
    }

    /**
     * Verify pushback webhook callback signature
     */
    public function verifyCallbackSignature(array $data, string $receivedSignature): bool
    {
        ksort($data);
        $b4hash = '';
        foreach ($data as $value) {
            if (is_array($value)) {
                $b4hash .= json_encode($value);
            } else {
                $b4hash .= (string)$value;
            }
        }
        $generated = base64_encode(hash_hmac('sha512', $b4hash, $this->apiKey, true));
        return hash_equals($generated, $receivedSignature);
    }

    /**
     * Check transaction status on PayWay server
     */
    public function checkTransaction(string $tranId): array
    {
        $reqTime = now()->utc()->format('YmdHis');
        $b4hash = $reqTime . $this->merchantId . $tranId;
        $hash = base64_encode(hash_hmac('sha512', $b4hash, $this->apiKey, true));

        $url = $this->environment === 'live'
            ? 'https://checkout.payway.com.kh/api/payment-gateway/v1/payments/check-transaction-2'
            : 'https://checkout-sandbox.payway.com.kh/api/payment-gateway/v1/payments/check-transaction-2';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'req_time' => $reqTime,
            'merchant_id' => $this->merchantId,
            'tran_id' => $tranId,
            'hash' => $hash,
        ]);

        return $response->json() ?? [];
    }

    public function getGatewayCode(): string
    {
        return 'abapayway';
    }

    public function isConfigured(): bool
    {
        return !empty($this->merchantId) && !empty($this->apiKey);
    }
}
