<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $productIds = DB::table('products')->pluck('id')->values();

        if ($productIds->count() < 2) {
            $this->command->warn('OrderSeeder skipped: not enough products in the database.');

            return;
        }
        $customer1 = Customer::where('email', 'tharyvireak@gmail.com')->first();
        $customer2 = Customer::where('email', 'john.doe@example.com')->first();
        if (DB::table('orders')->count() > 0) {
            $this->command->info('OrderSeeder skipped: orders already exist.');

            return;
        }
        $order1Id = DB::table('orders')->insertGetId([
            'customer_id' => $customer1?->id,
            'guest_email' => $customer1 ? null : 'tharyvireak@gmail.com',
            'vendor_id' => null,
            'total_amount' => 248.00,
            'coupon_code' => 'SAVE10',
            'discount_amount' => 27.55,
            'status' => 'completed',
            'payment_method' => 'cod',
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);

        DB::table('order_details')->insert([
            [
                'order_id' => $order1Id,
                'product_id' => $productIds[0],
                'quantity' => 2,
                'price' => 89.00,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ],
            [
                'order_id' => $order1Id,
                'product_id' => $productIds[1],
                'quantity' => 1,
                'price' => 70.00,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ],
        ]);

        DB::table('shipping_addresses')->insert([
            'order_id' => $order1Id,
            'customer_id' => $customer1?->id,
            'name' => 'Thary Vireak',
            'phone' => '+855 71 675 5350',
            'address' => '123 Norodom Blvd',
            'city' => 'Phnom Penh',
            'postal_code' => '120101',
            'country' => 'Cambodia',
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);
        $order2Id = DB::table('orders')->insertGetId([
            'customer_id' => $customer2?->id,
            'guest_email' => $customer2 ? null : 'john.doe@example.com',
            'vendor_id' => null,
            'total_amount' => 149.00,
            'coupon_code' => null,
            'discount_amount' => 0.00,
            'status' => 'processing',
            'payment_method' => 'aba_payway',
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        DB::table('order_details')->insert([
            [
                'order_id' => $order2Id,
                'product_id' => $productIds[2] ?? $productIds[0],
                'quantity' => 1,
                'price' => 149.00,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
        ]);

        DB::table('shipping_addresses')->insert([
            'order_id' => $order2Id,
            'customer_id' => $customer2?->id,
            'name' => 'John Doe',
            'phone' => '+1 555 000 1234',
            'address' => '456 Broadway Ave',
            'city' => 'New York',
            'postal_code' => '10001',
            'country' => 'USA',
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);
        $order3Id = DB::table('orders')->insertGetId([
            'customer_id' => null,
            'guest_email' => 'guest.shopper@example.com',
            'vendor_id' => null,
            'total_amount' => 55.00,
            'coupon_code' => null,
            'discount_amount' => 0.00,
            'status' => 'pending',
            'payment_method' => 'paypal',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        DB::table('order_details')->insert([
            [
                'order_id' => $order3Id,
                'product_id' => $productIds[3] ?? $productIds[0],
                'quantity' => 1,
                'price' => 35.00,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
            [
                'order_id' => $order3Id,
                'product_id' => $productIds[4] ?? $productIds[1],
                'quantity' => 1,
                'price' => 20.00,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
        ]);

        DB::table('shipping_addresses')->insert([
            'order_id' => $order3Id,
            'customer_id' => null,
            'name' => 'Guest Shopper',
            'phone' => '+855 12 000 999',
            'address' => '789 Kampuchea Krom Blvd',
            'city' => 'Phnom Penh',
            'postal_code' => '120201',
            'country' => 'Cambodia',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $this->command->info('OrderSeeder: 3 orders with details and shipping addresses seeded.');
    }
}
