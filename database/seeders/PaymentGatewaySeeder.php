<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use App\Models\PaymentGatewayConfig;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            [
                'name' => 'ABA PayWay',
                'code' => 'aba_payway',
                'description' => 'ABA Bank PayWay payment gateway for Cambodia.',
                'is_active' => true,
                'configs' => [
                    ['key_name' => 'merchant_id',  'key_value' => env('ABA_MERCHANT_ID', 'your-aba-merchant-id'),  'is_encrypted' => false, 'environment' => 'sandbox'],
                    ['key_name' => 'api_key',       'key_value' => env('ABA_API_KEY', 'your-aba-api-key'),       'is_encrypted' => true,  'environment' => 'sandbox'],
                    ['key_name' => 'api_url',       'key_value' => env('ABA_API_URL', 'https://checkout.payway.com.kh/api/payment-gateway/v1/payments/purchase'), 'is_encrypted' => false, 'environment' => 'sandbox'],
                ],
            ],
            [
                'name' => 'PayPal',
                'code' => 'paypal',
                'description' => 'PayPal checkout — worldwide online payments.',
                'is_active' => true,
                'configs' => [
                    ['key_name' => 'client_id',     'key_value' => env('PAYPAL_CLIENT_ID', 'your-paypal-client-id'),     'is_encrypted' => false, 'environment' => 'sandbox'],
                    ['key_name' => 'client_secret', 'key_value' => env('PAYPAL_CLIENT_SECRET', 'your-paypal-client-secret'), 'is_encrypted' => true,  'environment' => 'sandbox'],
                ],
            ],
            [
                'name' => 'Stripe',
                'code' => 'stripe',
                'description' => 'Stripe — online credit and debit card processing.',
                'is_active' => true,
                'configs' => [
                    ['key_name' => 'public_key',  'key_value' => env('STRIPE_PUBLIC', 'your-stripe-public-key'), 'is_encrypted' => false, 'environment' => 'sandbox'],
                    ['key_name' => 'secret_key',  'key_value' => env('STRIPE_SECRET', 'your-stripe-secret-key'), 'is_encrypted' => true,  'environment' => 'sandbox'],
                ],
            ],
            [
                'name' => 'Cash on Delivery',
                'code' => 'cod',
                'description' => 'Customer pays in cash when the order is delivered.',
                'is_active' => true,
                'configs' => [],
            ],
        ];

        foreach ($gateways as $data) {
            $gateway = PaymentGateway::firstOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_active' => $data['is_active'],
                ]
            );

            foreach ($data['configs'] as $cfg) {
                PaymentGatewayConfig::firstOrCreate(
                    [
                        'gateway_id' => $gateway->id,
                        'key_name' => $cfg['key_name'],
                    ],
                    [
                        'key_value' => $cfg['key_value'],
                        'is_encrypted' => $cfg['is_encrypted'],
                        'environment' => $cfg['environment'],
                    ]
                );
            }
        }

        $this->command->info('PaymentGatewaySeeder: '.count($gateways).' gateways seeded.');
    }
}
