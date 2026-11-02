<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RefundSeeder extends Seeder
{
    public function run(): void
    {
        $payment = Payment::where('status', 'completed')->first();

        if (! $payment) {
            $this->command->warn('RefundSeeder skipped: no completed payment found. Run PaymentSeeder first.');

            return;
        }
        Refund::firstOrCreate(
            ['payment_id' => $payment->id],
            [
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => 'requested',
                'refund_id' => 'REF-'.strtoupper(Str::random(10)),
                'reason' => 'Customer requested a return — item received in damaged condition.',
                'response' => [
                    'message' => 'Refund request submitted.',
                    'submitted_at' => now()->toIso8601String(),
                ],
            ]
        );

        $this->command->info('RefundSeeder: refund record seeded.');
    }
}
