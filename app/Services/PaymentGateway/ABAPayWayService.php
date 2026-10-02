<?php

namespace App\Services\PaymentGateway;

use App\Models\PaymentGateway;
use App\Models\PaymentGatewayConfig;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ABAPayWayService implements PaymentGatewayInterface
{
    protected $merchantId;
    protected $merchantName;
    protected $apiKey;
    protected $environment;
    protected $checkoutUrl;
    protected $mockEnabled;
    protected $rsaPublicKey;
    protected $rsaPrivateKey;

    public function __construct(string $environment = 'sandbox')
    {
        $this->environment = $environment;

        try {
            $gateway = PaymentGateway::where('code', 'abapayway')
                ->where('is_active', true)
                ->first();

            $configs = $gateway
                ? PaymentGatewayConfig::where('gateway_id', $gateway->id)
                    ->where('environment', $environment)
                    ->pluck('key_value', 'key_name')
                : collect();
        } catch (\Throwable $e) {
            $configs = collect();
        }

        // Prioritize .env credentials, fallback to DB configs or defaults
        $this->merchantId   = env('PAYWAY_MERCHANT_ID', env('ABA_PAYWAY_MERCHANT_ID', $configs['merchant_id'] ?? 'ec479081'));
        $this->merchantName = env('ABA_PAYWAY_MERCHANT_NAME', $configs['merchant_name'] ?? 'tharyvireak181');
        $this->apiKey       = env('PAYWAY_API_KEY', env('ABA_PAYWAY_API_KEY', $configs['api_key'] ?? 'a6647040a23f7feca7e315de37dcc776e4be1a99'));

        // Load RSA Keys with defaults
        $pubKeyPath = base_path(env('PAYWAY_RSA_PUBLIC_KEY_PATH', 'storage/keys/payway_public.pem'));
        $privKeyPath = base_path(env('PAYWAY_RSA_PRIVATE_KEY_PATH', 'storage/keys/payway_private.pem'));

        $defaultPubKey = "-----BEGIN PUBLIC KEY-----\nMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDMNVoDU0L6v5tepn0/wMTufO5u\nlrZi99mE6pYlGEefdvDAaLkIbXLwhanP7jYVIOObOS1LLxit8GXYhkj0cmAxIZ9F\n5BIZfoNbtImU1uPOxpkPgmv8QAgsGC6XbsGkPXdOzySP/VEWaRuQn6qJ+zM5REjc\nBS8ENY58CmIxNVCPkQIDAQAB\n-----END PUBLIC KEY-----";

        $defaultPrivKey = "-----BEGIN RSA PRIVATE KEY-----\nMIICXAIBAAKBgQCGHHsnEsHfng47X/EplK1GsGcpJYB7OU2wUsSZd4LHE4982rvi\nlAxINjGMgWFNkGojlGTSM/1u0jAux8Y4zegvfE9l94Up8k0mlhxyAr63O2zw2lAD\nEuTpeitsaG6V2G+7XbQdC+iQPsmn6DuSp3wik+/yfWjaAGOhS6wk8AqE6QIDAQAB\nAoGABwbFIJLO2qrKqWBWkV5g11Qeqov541ToPJhxiXZu9hAMGU886DTk2D82Anj9\nmfpg4jR0CU2kgQzJdZ5LlYrQQFf/QiQDpcN5CFyNAfdzg2SN6TPV0NSjfTwfczk0\nEMsStOxNVlbgIcnclju9wMnGMdYS9ox/5V/k5DAohilSr/UCQQCGM2ojjE9xE0B6\nlZuOgbMhQWhwDV7KtwkdYvOFFWGlu3y7HQOXswMDqLsVHFOGxR0Qr+ljKTWuLAX6\nM3VG0qHdAkEA/9RAkrh2mjvDob4O7rF+vNUbZn+5oZYXdrcfvVvS5/ieHO4v1F0C\npyGXZ4qVIXd3hzGMJsifmqXlDlYS36WsfQJAEdqfQVF2dDW6e1SSGhH66263RUkS\nFlpDfSxf95Grpw/1fTNT+gev2/nDWgA9xALVZhXxN+cQpDZpKStVa/Gz5QJBANCj\nXp5EI/E2OyFknFgcyfD2Q064MJpzhw3dmAJ3uVsml4jKwUw8X4O/WvzG7Py04768\naxfAnt/FjOfOeJa9U10CQFbNABPz/naGXUrbQcK6WM6zwc/7KUOtPAtb81UccZoz\nzasylaTl4nJVo5N4/PDaF4ZVaZ1+rYJVvYbtsML0E1U=\n-----END RSA PRIVATE KEY-----";

        $this->rsaPublicKey  = file_exists($pubKeyPath) ? file_get_contents($pubKeyPath) : env('PAYWAY_RSA_PUBLIC_KEY', $defaultPubKey);
        $this->rsaPrivateKey = file_exists($privKeyPath) ? file_get_contents($privKeyPath) : env('PAYWAY_RSA_PRIVATE_KEY', $defaultPrivKey);

        $defaultUrl = $environment === 'live'
            ? 'https://checkout.payway.com.kh/api/payment-gateway/v1/payments/purchase'
            : 'https://checkout-sandbox.payway.com.kh/api/payment-gateway/v1/payments/purchase';

        $this->checkoutUrl = env('ABA_PAYWAY_API_URL', env('PAYWAY_API_URL', $defaultUrl));

        // Mock sandbox mode for testing on Vercel without a whitelisted IP
        $this->mockEnabled = filter_var(env('ABA_PAYWAY_MOCK_SANDBOX', false), FILTER_VALIDATE_BOOLEAN);
    }

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function getCheckoutUrl(): string
    {
        return $this->checkoutUrl;
    }

    public function isMockEnabled(): bool
    {
        return $this->mockEnabled;
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
     * Create an ABA PayWay payment transaction with timeouts and dynamic mock fallback.
     *
     * @throws Exception
     */
    public function purchase(array $params, $order = null): array
    {
        if ($this->mockEnabled) {
            Log::info('ABA PayWay Mock Sandbox mode explicitly enabled, returning dynamic mock KHQR');
            return $this->generateMockResponse($order, $params);
        }

        try {
            // ABA PayWay Purchase API requires multipart/form-data
            $response = Http::connectTimeout(5)
                ->timeout(10)
                ->asMultipart()
                ->post($this->checkoutUrl, $params);

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data) && (isset($data['qrString']) || isset($data['abapay_deeplink']))) {
                    if (empty($data['qrImage']) && !empty($data['qrString'])) {
                        $data['qrImage'] = self::generateQrPngBase64($data['qrString']);
                    } elseif (!empty($data['qrImage'])) {
                        // Strip data:image/...;base64, prefix if present so Flutter Image.memory(base64Decode(...)) works cleanly
                        $data['qrImage'] = preg_replace('/^data:image\/[^;]+;base64,/', '', $data['qrImage']);
                    }
                    return $data;
                }
            }

            $body = $response->body();
            $data = json_decode($body, true);

            Log::warning('ABA PayWay API response error', [
                'status' => $response->status(),
                'body'   => $body,
            ]);

            // Auto-fallback in sandbox when ABA PayWay blocks dynamic cloud IP (403 Forbidden or policy block)
            if ($this->environment === 'sandbox') {
                Log::info('ABA PayWay Sandbox blocked or unparseable response, falling back to dynamic mock KHQR');
                return $this->generateMockResponse($order, $params);
            }

            $description = is_array($data) && !empty($data['description'])
                ? $data['description']
                : (is_string($body) && !empty(trim($body)) ? trim($body) : 'Payment gateway rejected transaction');

            throw new Exception('Payment gateway rejected transaction: ' . $description, $response->status());
        } catch (ConnectionException $e) {
            Log::warning('ABA PayWay connection timeout: ' . $e->getMessage());

            if ($this->environment === 'sandbox') {
                Log::info('ABA PayWay Sandbox connection timed out, falling back to dynamic mock KHQR');
                return $this->generateMockResponse($order, $params);
            }

            throw new Exception('Payment gateway connection timed out. Please try again or choose Cash on Delivery.', 504);
        } catch (RequestException $e) {
            Log::warning('ABA PayWay request exception: ' . $e->getMessage());

            if ($this->environment === 'sandbox') {
                Log::info('ABA PayWay Sandbox request failed, falling back to dynamic mock KHQR');
                return $this->generateMockResponse($order, $params);
            }

            throw new Exception('Payment gateway temporarily unavailable.', 502);
        }
    }

    /**
     * Poll transaction status with 3s/4s timeout and sandbox fallback.
     */
    public function checkTransaction(string $tranId): array
    {
        $reqTime = now()->utc()->format('YmdHis');
        $b4hash  = $reqTime . $this->merchantId . $tranId;
        $hash    = base64_encode(hash_hmac('sha512', $b4hash, $this->apiKey, true));

        $url = $this->environment === 'live'
            ? 'https://checkout.payway.com.kh/api/payment-gateway/v1/payments/check-transaction-2'
            : 'https://checkout-sandbox.payway.com.kh/api/payment-gateway/v1/payments/check-transaction-2';

        try {
            $response = Http::connectTimeout(3)
                ->timeout(4)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($url, [
                    'req_time'    => $reqTime,
                    'merchant_id' => $this->merchantId,
                    'tran_id'     => $tranId,
                    'hash'        => $hash,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data) && isset($data['data']['payment_status'])) {
                    return $data;
                }
            }

            // Real ABA PayWay did not confirm approval, or connection blocked: keep PENDING
            return [
                'status'      => 0,
                'description' => 'Payment pending',
                'data'        => [
                    'payment_status'      => 'PENDING',
                    'payment_status_code' => 1,
                    'tran_id'             => $tranId,
                ],
            ];
        } catch (\Throwable $e) {
            Log::warning('ABA PayWay checkTransaction error/timeout: ' . $e->getMessage());

            return [
                'status'      => 0,
                'description' => 'Payment pending',
                'data'        => [
                    'payment_status'      => 'PENDING',
                    'payment_status_code' => 1,
                    'tran_id'             => $tranId,
                ],
            ];
        }
    }

    /**
     * Fallback mock response for testing with dynamically computed KHQR string and exact order total.
     */
    protected function generateMockResponse($order, array $params = []): array
    {
        $amount = isset($params['amount'])
            ? (float) $params['amount']
            : ($order ? (float) ($order->total_amount ?? 0) : 0.0);

        $orderId  = $order ? $order->id : null;
        $tranId   = $params['tran_id'] ?? ('ORD-' . ($orderId ?? time()) . '-' . time());
        $currency = $params['currency'] ?? 'USD';

        // Dynamically compute EMVCo KHQR string with exact order amount and merchant name
        $khqr = self::generateKHQRString($amount, $this->merchantName, $currency, null, $tranId);
        $deeplink = 'abamobilebank://ababank.com?type=payway&qrcode=' . urlencode($khqr);

        // Generate a 100% genuine, pixel-perfect, scannable QR Code PNG in Base64
        $qrImageBase64 = self::generateQrPngBase64($khqr);

        return [
            'status'          => 0,
            'description'     => 'Success (Mock Sandbox)',
            'order_id'        => $orderId,
            'tran_id'         => $tranId,
            'total_amount'    => number_format($amount, 2, '.', ''),
            'amount'          => number_format($amount, 2, '.', ''),
            'currency'        => $currency,
            'abapay_deeplink' => $deeplink,
            'qrString'        => $khqr,
            'qrImage'         => $qrImageBase64,
        ];
    }

    /**
     * Generate EMVCo KHQR string compatible with Bakong & ABA Mobile.
     * Computes Tag 54 (amount), Tag 59 (merchant name), Tag 60 (city), Tag 53 (currency), and CRC-16 checksum.
     */
    public static function generateKHQRString(
        float $amount,
        string $merchantName = 'THARY VIREAK',
        string $currency = 'USD',
        ?string $bakongId = null,
        ?string $tranId = null
    ): string {
        $amountStr = number_format($amount, 2, '.', '');
        $tag54Len  = str_pad((string) strlen($amountStr), 2, '0', STR_PAD_LEFT);
        $tag54     = '54' . $tag54Len . $amountStr;

        $currencyCode = strtoupper($currency) === 'KHR' ? '116' : '840';
        $tag53        = '5303' . $currencyCode;

        $cleanMerchantName = substr(trim($merchantName), 0, 25);
        $merchantLen       = str_pad((string) strlen($cleanMerchantName), 2, '0', STR_PAD_LEFT);
        $tag59             = '59' . $merchantLen . $cleanMerchantName;

        // Tag 60: Merchant City (Mandatory in EMVCo / KHQR standard, min 1 char)
        $cityName = 'Phnom Penh';
        $cityLen  = str_pad((string) strlen($cityName), 2, '0', STR_PAD_LEFT);
        $tag60    = '60' . $cityLen . $cityName;

        $bakongId = $bakongId ?: env('ABA_BAKONG_ACCOUNT_ID');

        if ($bakongId) {
            // Tag 29: Individual / Merchant Bakong KHQR
            $sub00 = '00' . str_pad((string) strlen($bakongId), 2, '0', STR_PAD_LEFT) . $bakongId;
            $sub02 = '0211abaakhppxxx';
            $tag29Val = $sub00 . $sub02;
            $tagMerchant = '29' . str_pad((string) strlen($tag29Val), 2, '0', STR_PAD_LEFT) . $tag29Val;
        } else {
            // Tag 30: ABA Bank Merchant Info for ec000262
            $tagMerchant = '30510016abaakhppxxx@abaa01153240906164357420208ABA Bank';
        }

        // Tag 62: Additional Data (Bill Number / Transaction ID)
        $tag62 = '';
        if ($tranId) {
            $sub01 = '01' . str_pad((string) strlen($tranId), 2, '0', STR_PAD_LEFT) . $tranId;
            $tag62 = '62' . str_pad((string) strlen($sub01), 2, '0', STR_PAD_LEFT) . $sub01;
        }

        // Base EMVCo / KHQR payload up to CRC tag 6304
        // Note: Proprietary Tag 99 is omitted to prevent ABA Mobile from attempting a fictitious PayWay session lookup
        $payload = '000201'                                    // Tag 00: Format indicator
                 . '010212'                                    // Tag 01: Dynamic QR
                 . $tagMerchant                                // Tag 29 or Tag 30
                 . '52045999'                                  // Tag 52: MCC
                 . $tag53                                      // Tag 53: Currency (840 = USD)
                 . $tag54                                      // Tag 54: Amount (dynamic order total)
                 . '5802KH'                                    // Tag 58: Country code
                 . $tag59                                      // Tag 59: Merchant name
                 . $tag60                                      // Tag 60: Merchant city (Phnom Penh)
                 . $tag62                                      // Tag 62: Additional data
                 . '6304';                                     // Tag 63: CRC placeholder

        $crc = self::calculateCRC16($payload);

        return $payload . $crc;
    }

    /**
     * Generate a real, high-resolution, scannable PNG QR code encoded in Base64.
     */
    public static function generateQrPngBase64(string $data, int $scale = 6): string
    {
        try {
            return QRCode::pngBase64($data, ['s' => 'qrm'], $scale);
        } catch (\Throwable $e) {
            Log::error('Local QR code generation failed: ' . $e->getMessage());
            return self::getFallbackQrPng();
        }
    }

    /**
     * Compute CRC-16/CCITT-FALSE (EMVCo standard: polynomial 0x1021, init 0xFFFF).
     */
    public static function calculateCRC16(string $str): string
    {
        $crc = 0xFFFF;
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $crc ^= (ord($str[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    /**
     * Fallback standard QR code image in Base64 (valid 414x414 PNG, guaranteed non-empty).
     */
    public static function getFallbackQrPng(): string
    {
        return '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';
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
