<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Order;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle events after all outer database transactions have been committed.
     * Ensures order details and shipping address records exist before notification.
     *
     * @var bool
     */
    public $afterCommit = true;

    public function __construct(
        protected TelegramService $telegramService
    ) {}

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        try {
            $isCompleted = strtolower((string) $order->status) === 'completed';
            $isCod = in_array(strtolower((string) $order->payment_method), ['cod', 'cash_on_delivery']);

            // Immediate checkout success for Cash on Delivery or orders created directly as completed (e.g. PayPal)
            if ($isCompleted || $isCod) {
                $this->telegramService->sendOrderNotification($order, 'checkout_success');
            }
        } catch (\Throwable $e) {
            Log::error('OrderObserver created error: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        try {
            // When payment is approved / confirmed (e.g. ABA PayWay webhook, status poll, or gateway capture)
            if ($order->wasChanged('status') && strtolower((string) $order->status) === 'completed') {
                $this->telegramService->sendOrderNotification($order, 'checkout_success');
            }
        } catch (\Throwable $e) {
            Log::error('OrderObserver updated error: ' . $e->getMessage());
        }
    }
}
