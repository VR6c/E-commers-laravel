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
        $gatewayId = DB::table('payment_gateways')->insertGetId([
            'name' => 'ABA PayWay',
            'code' => 'abapayway',
            'description' => 'Accept payments via ABA PAY, KHQR, Credit/Debit cards, WeChat Pay and Alipay',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('payment_gateway_configs')->insert([
            [
                'gateway_id' => $gatewayId,
                'key_name' => 'merchant_id',
                'key_value' => 'ec476341',
                'is_encrypted' => false,
                'environment' => 'sandbox',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'gateway_id' => $gatewayId,
                'key_name' => 'api_key',
                'key_value' => '18e940724353f94ae7b77f4a59cb1fe76bd1e140',
                'is_encrypted' => true,
                'environment' => 'sandbox',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $gateway = DB::table('payment_gateways')->where('code', 'abapayway')->first();
        if ($gateway) {
            DB::table('payment_gateway_configs')->where('gateway_id', $gateway->id)->delete();
            DB::table('payment_gateways')->where('id', $gateway->id)->delete();
        }
    }
};
