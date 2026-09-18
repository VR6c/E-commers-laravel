<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Index on order_details.product_id for order lookup by product (e.g. recipe unlock checks)
        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                $table->index('product_id', 'idx_od_product_id');
            });
        }

        // 2. Composite index on product_attribute_values for quick product-attribute lookups
        if (Schema::hasTable('product_attribute_values')) {
            Schema::table('product_attribute_values', function (Blueprint $table) {
                $table->index(['product_id', 'attribute_value_id'], 'idx_pav_product_attribute');
            });
        }

        // 3. Composite index on product_variant_attribute_values for reverse lookup in shop filter
        if (Schema::hasTable('product_variant_attribute_values')) {
            Schema::table('product_variant_attribute_values', function (Blueprint $table) {
                $table->index(['attribute_value_id', 'product_variant_id'], 'idx_pvav_attr_val_variant');
            });
        }

        // 4. Composite indexes on recipes for catalog filtering
        if (Schema::hasTable('recipes')) {
            Schema::table('recipes', function (Blueprint $table) {
                $table->index(['is_active', 'cuisine'], 'idx_recipes_active_cuisine');
                $table->index(['is_active', 'difficulty'], 'idx_recipes_active_difficulty');
            });
        }

        // 5. Composite index on coupons for fast code lookup with status
        if (Schema::hasTable('coupons')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->index(['code', 'status'], 'idx_coupons_code_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                $table->dropIndex('idx_od_product_id');
            });
        }

        if (Schema::hasTable('product_attribute_values')) {
            Schema::table('product_attribute_values', function (Blueprint $table) {
                $table->dropIndex('idx_pav_product_attribute');
            });
        }

        if (Schema::hasTable('product_variant_attribute_values')) {
            Schema::table('product_variant_attribute_values', function (Blueprint $table) {
                $table->dropIndex('idx_pvav_attr_val_variant');
            });
        }

        if (Schema::hasTable('recipes')) {
            Schema::table('recipes', function (Blueprint $table) {
                $table->dropIndex('idx_recipes_active_cuisine');
                $table->dropIndex('idx_recipes_active_difficulty');
            });
        }

        if (Schema::hasTable('coupons')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropIndex('idx_coupons_code_status');
            });
        }
    }
};
