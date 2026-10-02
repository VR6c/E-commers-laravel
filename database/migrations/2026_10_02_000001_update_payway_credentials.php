<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $gateway = DB::table('payment_gateways')->where('code', 'abapayway')->first();
        if ($gateway) {
            DB::table('payment_gateway_configs')
                ->where('gateway_id', $gateway->id)
                ->where('key_name', 'merchant_id')
                ->update(['key_value' => 'ec476922', 'updated_at' => now()]);

            DB::table('payment_gateway_configs')
                ->where('gateway_id', $gateway->id)
                ->where('key_name', 'api_key')
                ->update(['key_value' => 'fde4f7ade6a1eab1c6fdb05c7823db3804ab702d', 'updated_at' => now()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
