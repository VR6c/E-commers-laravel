<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'name' => 'US Dollar',
                'code' => 'USD',
                'symbol' => '$',
                'exchange_rate' => 1.0000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cambodian Riel',
                'code' => 'KHR',
                'symbol' => '៛',
                'exchange_rate' => 4100.0000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Euro',
                'code' => 'EUR',
                'symbol' => '€',
                'exchange_rate' => 0.9200,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'British Pound',
                'code' => 'GBP',
                'symbol' => '£',
                'exchange_rate' => 0.7900,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Thai Baht',
                'code' => 'THB',
                'symbol' => '฿',
                'exchange_rate' => 36.5000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('currencies')->insertOrIgnore($currencies);

        $this->command->info('CurrencySeeder: '.count($currencies).' currencies seeded.');
    }
}
