<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();

        if (! $admin) {
            $this->command->warn('PaymentSeeder skipped: admin user not found. Run AdminSeeder first.');

            return;
        }

        $orders = Order::orderBy('id')->take(3)->get();

        if ($orders->isEmpty()) {
            $this->command->warn('PaymentSeeder skipped: no orders found. Run OrderSeeder first.');

            return;
        }

        $gatewayMap = PaymentGateway::whereIn('code', ['cod', 'aba_payway', 'paypal'])
            ->get()
            ->keyBy('code');

        $paymentData = [
            [
                'order' => $orders->get(0),
                'gateway_code' => 'cod',
                'currency' => 'USD',
                'status' => 'completed',
                'transaction_id' => 'COD-'.strtoupper(Str::random(10)),
                'response' => ['message' => 'Cash collected on delivery'],
                'meta' => ['collected_by' => 'delivery_agent'],
            ],
            [
                'order' => $orders->get(1),
                'gateway_code' => 'aba_payway',
                'currency' => 'USD',
                'status' => 'processing',
                'transaction_id' => 'ABA-'.strtoupper(Str::random(12)),
                'response' => ['message' => 'Payment initiated, awaiting confirmation'],
                'meta' => ['channel' => 'aba_payway_checkout'],
            ],
            [
                'order' => $orders->get(2),
                'gateway_code' => 'paypal',
                'currency' => 'USD',
                'status' => 'pending',
                'transaction_id' => 'PP-'.strtoupper(Str::random(12)),
                'response' => ['message' => 'Awaiting PayPal confirmation'],
                'meta' => ['paypal_env' => 'sandbox'],
            ],
        ];

        foreach ($paymentData as $data) {
            $order = $data['order'];
            $gateway = $gatewayMap->get($data['gateway_code']);

            if (! $order || ! $gateway) {
                continue;
            }

            Payment::firstOrCreate(
                ['order_id' => $order->id, 'gateway_id' => $gateway->id],
                [
                    'user_id' => $admin->id,
                    'amount' => $order->total_amount,
                    'currency' => $data['currency'],
                    'status' => $data['status'],
                    'transaction_id' => $data['transaction_id'],
                    'response' => $data['response'],
                    'meta' => $data['meta'],
                ]
            );
        }

        $this->command->info('PaymentSeeder: payment records seeded for '.count($paymentData).' orders.');
    }
}
