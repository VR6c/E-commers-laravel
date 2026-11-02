<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // GET /api/orders: WHERE customer_id = ? ORDER BY created_at DESC
            $table->index(['customer_id', 'created_at'], 'idx_orders_customer_created');
            // Admin dashboard: WHERE status = 'completed' AND created_at >= ? (charts, sums)
            $table->index(['status', 'created_at'], 'idx_orders_status_created');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_customer_created');
            $table->dropIndex('idx_orders_status_created');
        });
    }
};
