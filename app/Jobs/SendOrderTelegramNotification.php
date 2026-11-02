<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Order;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

class SendOrderTelegramNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $orderId,
        public readonly string $event = 'checkout_success'
    ) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 60];
    }

    public function handle(TelegramService $telegram): void
    {
        if (! $telegram->isEnabled() || ! $telegram->isConfigured()) {
            return;
        }

        $order = Order::find($this->orderId);
        if (! $order) {
            return;
        }

        // The service deduplicates per order/event and returns false on a failed send.
        if (! $telegram->sendOrderNotification($order, $this->event)) {
            throw new RuntimeException("Telegram notification failed for order #{$this->orderId}.");
        }
    }
}
