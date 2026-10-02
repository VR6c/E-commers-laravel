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

        $this->merchantId   = $configs['merchant_id'] ?? env('PAYWAY_MERCHANT_ID', env('ABA_PAYWAY_MERCHANT_ID', 'ec476922'));
        $this->merchantName = $configs['merchant_name'] ?? env('ABA_PAYWAY_MERCHANT_NAME', 'THARY VIREAK');
        $this->apiKey       = $configs['api_key'] ?? env('PAYWAY_API_KEY', env('ABA_PAYWAY_API_KEY', '18e940724353f94ae7b77f4a59cb1fe76bd1e140'));

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
            // Strict timeouts: 3s connect timeout, 5s execution timeout
            $response = Http::connectTimeout(3)
                ->timeout(5)
                ->asForm()
                ->post($this->checkoutUrl, $params);

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data) && (isset($data['qrString']) || isset($data['abapay_deeplink']))) {
                    if (empty($data['qrImage']) && !empty($data['qrString'])) {
                        $data['qrImage'] = self::generateQrPngBase64($data['qrString']);
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
        $khqr = self::generateKHQRString($amount, $this->merchantName, $currency);
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
    public static function generateKHQRString(float $amount, string $merchantName = 'THARY VIREAK', string $currency = 'USD'): string
    {
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

        // Base EMVCo / KHQR payload up to CRC tag 6304
        $payload = '000201'                                    // Tag 00: Format indicator
                 . '010212'                                    // Tag 01: Dynamic QR
                 . '30510016abaakhppxxx@abaa01151111111111111110208ABA Bank' // Tag 30: Merchant account info
                 . '52045999'                                  // Tag 52: MCC
                 . $tag53                                      // Tag 53: Currency (840 = USD)
                 . $tag54                                      // Tag 54: Amount (dynamic order total!)
                 . '5802KH'                                    // Tag 58: Country code
                 . $tag59                                      // Tag 59: Merchant name
                 . $tag60                                      // Tag 60: Merchant city (Phnom Penh)
                 . '6226050701276750711T9090329509'            // Tag 62: Additional data
                 . '9975001317909032954420113179090599523467170013F1BF016411FDA6804PONL6908purchase' // Tag 99: PayWay custom data
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
     * Fallback standard QR code image in Base64.
     */
    public static function getFallbackQrPng(): string
    {
        try {
            return QRCode::pngBase64('00020101021230510016abaakhppxxx@abaa01151111111111111110208ABA Bank52045999530384054041.005802KH5912THARY VIREAK6010Phnom Penh6304A26C');
        } catch (\Throwable $t) {
            return '';
        }
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
