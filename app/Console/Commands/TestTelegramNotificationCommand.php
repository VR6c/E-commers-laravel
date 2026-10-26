<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class TestTelegramNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test 
                            {message? : Optional custom text message to send}
                            {--sample-order : Force sending a sample checkout notification alert}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Telegram bot connectivity and send a sample checkout notification alert';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegram): int
    {
        $this->info('====================================================');
        $this->info('  Telegram Checkout Notification Diagnostic & Test  ');
        $this->info('====================================================');

        $botToken = $telegram->getBotToken();
        $chatId = $telegram->getChatId();
        $enabled = $telegram->isEnabled();

        $this->line('• Enabled in config:   ' . ($enabled ? '<fg=green>YES</>' : '<fg=red>NO (Set TELEGRAM_NOTIFICATIONS_ENABLED=true)</>'));
        $this->line('• Bot Token:           ' . (! empty($botToken) ? '<fg=green>' . substr($botToken, 0, 9) . '...' . substr($botToken, -5) . '</>' : '<fg=red>NOT SET (TELEGRAM_BOT_TOKEN in .env)</>'));
        $this->line('• Target Chat ID:      ' . (! empty($chatId) ? "<fg=green>{$chatId}</>" : '<fg=red>NOT SET (TELEGRAM_CHAT_ID in .env)</>'));
        $this->newLine();

        if (! $telegram->isConfigured()) {
            $this->error('Telegram bot credentials are missing in your .env file!');
            $this->newLine();
            $this->warn('Quick Setup Instructions:');
            $this->line('1. Open Telegram and search for @BotFather');
            $this->line('2. Send /newbot and follow instructions to create a bot.');
            $this->line('3. Copy the HTTP API token provided by BotFather.');
            $this->line('4. Send /start to your new bot in Telegram (or add it to your group/channel as Admin).');
            $this->line('5. Find your Chat ID (using @userinfobot or @RawDataBot in Telegram).');
            $this->line('6. Add the credentials to your .env file:');
            $this->newLine();
            $this->line('   <fg=yellow>TELEGRAM_BOT_TOKEN=123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ</>');
            $this->line('   <fg=yellow>TELEGRAM_CHAT_ID=123456789</>');
            $this->line('   <fg=yellow>TELEGRAM_NOTIFICATIONS_ENABLED=true</>');
            $this->newLine();
            return self::FAILURE;
        }

        // Test Bot Connection
        $this->info('Connecting to Telegram Bot API...');
        $botInfo = $telegram->getMe();

        if (! $botInfo['success']) {
            $this->error('Failed to connect to Telegram API: ' . ($botInfo['message'] ?? 'Unknown error'));
            $this->warn('Please double-check that your TELEGRAM_BOT_TOKEN is correct.');
            return self::FAILURE;
        }

        $botData = $botInfo['data'] ?? [];
        $botUsername = $botData['username'] ?? 'Unknown';
        $botFirstName = $botData['first_name'] ?? 'Bot';

        $this->info("Connected successfully as: @{$botUsername} ({$botFirstName})");
        $this->newLine();

        $customMessage = $this->argument('message');
        $forceSample = $this->option('sample-order');

        if ($customMessage && ! $forceSample) {
            $this->info("Sending custom test message to Chat ID [{$chatId}]...");
            $result = $telegram->sendMessage($customMessage);
        } else {
            $this->info("Generating sample checkout notification alert for Chat ID [{$chatId}]...");

            // Fetch the latest existing order or build a realistic dummy model
            $order = Order::with(['details.product', 'shippingAddress', 'customer'])->latest('id')->first();

            if (! $order) {
                // Mock an order object for test display
                $order = new Order([
                    'total_amount'    => 129.99,
                    'status'          => 'completed',
                    'payment_method'  => 'abapayway',
                    'coupon_code'     => 'WELCOME10',
                    'discount_amount' => 10.00,
                ]);
                $order->id = 9999;
                $order->created_at = now();
            }

            $message = $telegram->formatOrderMessage($order, 'checkout_success');
            $result = $telegram->sendMessage($message);
        }

        if ($result['success']) {
            $this->info('Notification sent successfully! Check your Telegram chat.');
            return self::SUCCESS;
        }

        $this->error('Failed to send message: ' . ($result['message'] ?? 'Unknown error'));
        $this->newLine();
        $this->warn('Troubleshooting tips:');
        $this->line('• If error is "Chat not found" or "bot was blocked by the user":');
        $this->line("  You MUST search for your bot in Telegram and tap 'Start' to allow it to message you.");
        $this->line('• If sending to a group/channel:');
        $this->line('  Make sure you added the bot into the group/channel, and gave it Admin permission to post messages.');
        $this->line('• Ensure the TELEGRAM_CHAT_ID includes the minus sign if it is a group/channel (e.g. -100xxxxxxxxxx).');

        return self::FAILURE;
    }
}
