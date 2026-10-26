<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Currency;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected ?string $botToken;
    protected ?string $chatId;
    protected bool $enabled;

    public function __construct(?string $botToken = null, ?string $chatId = null)
    {
        $this->botToken = $botToken ?: (string) config('services.telegram.bot_token', '');
        $this->chatId = $chatId ?: (string) config('services.telegram.chat_id', '');
        $this->enabled = (bool) config('services.telegram.enabled', true);
    }

    /**
     * Check if Telegram notifications are enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Check if both bot token and chat ID are configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->botToken) && ! empty($this->chatId);
    }

    /**
     * Get the configured Bot Token.
     */
    public function getBotToken(): ?string
    {
        return $this->botToken;
    }

    /**
     * Get the configured Chat ID.
     */
    public function getChatId(): ?string
    {
        return $this->chatId;
    }

    /**
     * Test the Telegram bot token and fetch bot profile details.
     */
    public function getMe(): array
    {
        if (empty($this->botToken)) {
            return [
                'success' => false,
                'message' => 'TELEGRAM_BOT_TOKEN is not configured.',
            ];
        }

        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/getMe";
            $http = Http::timeout(8);
            if (app()->environment('local')) {
                $http = $http->withoutVerifying();
            }
            $response = $http->get($url);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json('result', []),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('description', 'Failed to communicate with Telegram API.'),
                'status'  => $response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a raw message to Telegram chat.
     */
    public function sendMessage(string $message, ?string $chatId = null, string $parseMode = 'HTML'): array
    {
        if (! $this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'Telegram notifications are disabled via configuration.',
            ];
        }

        $targetChatId = $chatId ?: $this->chatId;

        if (empty($this->botToken) || empty($targetChatId)) {
            Log::warning('TelegramService: Cannot send message. Missing bot_token or chat_id.');
            return [
                'success' => false,
                'message' => 'Missing Telegram bot token or chat ID.',
            ];
        }

        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

            $payload = [
                'chat_id'                  => $targetChatId,
                'text'                     => $message,
                'parse_mode'               => $parseMode,
                'disable_web_page_preview' => true,
            ];

            $http = Http::timeout(10);
            if (app()->environment('local')) {
                $http = $http->withoutVerifying();
            }
            $response = $http->post($url, $payload);

            if ($response->successful()) {
                Log::info("TelegramService: Alert sent successfully to chat {$targetChatId}.");
                return [
                    'success'  => true,
                    'response' => $response->json(),
                ];
            }

            $data = $response->json();

            // Handle Telegram group migration to supergroup
            if (isset($data['parameters']['migrate_to_chat_id'])) {
                $migratedChatId = (string) $data['parameters']['migrate_to_chat_id'];
                Log::info("TelegramService: Chat migrated to supergroup {$migratedChatId}. Retrying...");
                $payload['chat_id'] = $migratedChatId;
                $retryResp = $http->post($url, $payload);
                if ($retryResp->successful()) {
                    return [
                        'success'  => true,
                        'response' => $retryResp->json(),
                    ];
                }
                $data = $retryResp->json();
            }

            // Fallback: If chat not found and ID starts with '-' but not '-100' (e.g. -5435329599 -> -1005435329599)
            if (str_starts_with((string) $targetChatId, '-') && ! str_starts_with((string) $targetChatId, '-100')) {
                $supergroupId = '-100' . substr((string) $targetChatId, 1);
                $payload['chat_id'] = $supergroupId;
                $supergroupResp = $http->post($url, $payload);
                if ($supergroupResp->successful()) {
                    Log::info("TelegramService: Alert sent successfully using supergroup ID {$supergroupId}.");
                    return [
                        'success'  => true,
                        'response' => $supergroupResp->json(),
                    ];
                }
            }

            $description = $data['description'] ?? 'Unknown Telegram API error';
            Log::error("TelegramService: Failed to send message. Telegram response: {$description} (HTTP {$response->status()})");

            return [
                'success' => false,
                'message' => $description,
                'status'  => $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error('TelegramService Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a formatted Telegram notification for a successful checkout / completed order.
     */
    public function sendOrderNotification(Order $order, string $event = 'checkout_success'): bool
    {
        if (! $this->isEnabled() || ! $this->isConfigured()) {
            return false;
        }

        // Deduplication: prevent multiple webhooks/polling triggers from spamming the chat
        $cacheKey = "telegram_order_notified_{$order->id}_{$event}";
        if (! Cache::add($cacheKey, now()->toIso8601String(), now()->addDays(7))) {
            Log::info("TelegramService: Notification for Order #{$order->id} ({$event}) was already sent. Skipping duplicate.");
            return true;
        }

        try {
            // Eager-load relations to build full details
            $order->loadMissing(['details.product', 'shippingAddress', 'customer']);

            $text = $this->formatOrderMessage($order, $event);
            $result = $this->sendMessage($text);

            if (! $result['success']) {
                // Remove deduplication cache key on error so a retry can occur if needed
                Cache::forget($cacheKey);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Cache::forget($cacheKey);
            Log::error("TelegramService: Failed to send order notification for #{$order->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format a rich, readable HTML message for the Telegram alert.
     */
    public function formatOrderMessage(Order $order, string $event = 'checkout_success'): string
    {
        $symbol = Currency::first()?->symbol ?? '$';
        $orderId = $order->id;
        $orderDate = $order->created_at ? $order->created_at->timezone('Asia/Phnom_Penh')->format('Y-m-d h:i A') : now()->timezone('Asia/Phnom_Penh')->format('Y-m-d h:i A');

        // Payment method display label
        $methodKey = strtolower((string) ($order->payment_method ?? 'cod'));
        $paymentLabels = [
            'cod'              => '💵 Cash on Delivery (COD)',
            'cash_on_delivery' => '💵 Cash on Delivery (COD)',
            'abapayway'        => '🇰🇭 ABA PayWay (KHQR)',
            'aba_payway'       => '🇰🇭 ABA PayWay (KHQR)',
            'paypal'           => '🅿️ PayPal',
            'stripe'           => '💳 Stripe Card',
        ];
        $paymentLabel = $paymentLabels[$methodKey] ?? '💳 ' . strtoupper($methodKey);

        // Status badge
        $status = ucfirst((string) $order->status);
        $statusEmoji = match (strtolower((string) $order->status)) {
            'completed'  => '✅ Paid & Completed',
            'processing' => '🔄 Processing',
            'pending'    => '⏳ Pending Confirmation',
            'canceled'   => '❌ Canceled',
            default      => '📦 ' . $status,
        };

        // Customer details
        $shipping = $order->shippingAddress;
        $customerName = htmlspecialchars($shipping?->name ?: ($order->customer?->name ?: 'Guest Customer'));
        $customerPhone = htmlspecialchars($shipping?->phone ?: ($order->customer?->phone ?: 'N/A'));
        $customerEmail = htmlspecialchars($order->guest_email ?: ($order->customer?->email ?: 'N/A'));

        // Address
        $addressParts = array_filter([
            $shipping?->address,
            $shipping?->suite,
            $shipping?->city,
            $shipping?->state,
            $shipping?->postal_code,
            $shipping?->country,
        ]);
        $fullAddress = htmlspecialchars(! empty($addressParts) ? implode(', ', $addressParts) : 'Not provided');

        // Header
        $isCod = in_array($methodKey, ['cod', 'cash_on_delivery']);
        $headerTitle = $isCod ? '🛍️ <b>NEW ORDER PLACED (COD)</b>' : '🎉 <b>CHECKOUT SUCCESSFUL!</b>';

        $lines = [];
        $lines[] = $headerTitle;
        $lines[] = '━━━━━━━━━━━━━━━━━━━━━━';
        $lines[] = "🆔 <b>Order ID:</b> <code>#{$orderId}</code>";
        $lines[] = "📅 <b>Date:</b> {$orderDate}";
        $lines[] = "📌 <b>Status:</b> {$statusEmoji}";
        $lines[] = "💳 <b>Payment:</b> {$paymentLabel}";
        $lines[] = '';

        $lines[] = '👤 <b>Customer Information:</b>';
        $lines[] = "• <b>Name:</b> {$customerName}";
        $lines[] = "• <b>Phone:</b> <code>{$customerPhone}</code>";
        if ($customerEmail !== 'N/A') {
            $lines[] = "• <b>Email:</b> {$customerEmail}";
        }
        $lines[] = "📍 <b>Shipping Address:</b> {$fullAddress}";
        $lines[] = '';

        // Line items
        $lines[] = '📦 <b>Order Items:</b>';
        if ($order->details && $order->details->isNotEmpty()) {
            foreach ($order->details as $item) {
                $productName = htmlspecialchars($item->product?->name ?? 'Product');
                $qty = (int) $item->quantity;
                $price = number_format((float) $item->price, 2);
                $itemTotal = number_format((float) ($item->price * $item->quantity), 2);
                $lines[] = "• {$productName} × {$qty} (<i>{$symbol}{$price}</i>) = <b>{$symbol}{$itemTotal}</b>";
            }
        } else {
            $lines[] = '• <i>Items details unavailable</i>';
        }

        $lines[] = '━━━━━━━━━━━━━━━━━━━━━━';

        // Discount / Coupon info
        if ($order->discount_amount && (float) $order->discount_amount > 0) {
            $discountFormatted = number_format((float) $order->discount_amount, 2);
            $couponText = $order->coupon_code ? " ({$order->coupon_code})" : '';
            $lines[] = "🎟️ <b>Discount{$couponText}:</b> -{$symbol}{$discountFormatted}";
        }

        $totalFormatted = number_format((float) $order->total_amount, 2);
        $lines[] = "💰 <b>Total Amount:</b> <b>{$symbol}{$totalFormatted}</b>";
        $lines[] = '━━━━━━━━━━━━━━━━━━━━━━';

        // Optional admin link
        $appUrl = rtrim((string) config('app.url', 'http://localhost'), '/');
        $adminOrderUrl = "{$appUrl}/admin/orders";
        $lines[] = "🔗 <a href=\"{$adminOrderUrl}\">View in Admin Panel</a>";

        return implode("\n", $lines);
    }
}
