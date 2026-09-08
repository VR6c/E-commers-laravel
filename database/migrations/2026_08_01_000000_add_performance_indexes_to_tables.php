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
        // Add performance indexes to products table
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id', 'idx_products_category_id');
            $table->index('brand_id', 'idx_products_brand_id');
            $table->index('status', 'idx_products_status');
            $table->index(['status', 'category_id'], 'idx_products_status_category');
            $table->index(['status', 'brand_id'], 'idx_products_status_brand');
            $table->index(['status', 'created_at'], 'idx_products_status_created');
        });

        // Add performance indexes to product_variants table
        Schema::table('product_variants', function (Blueprint $table) {
            $table->index(['product_id', 'is_primary'], 'idx_pv_product_primary');
            $table->index(['is_primary', 'price'], 'idx_pv_primary_price');
            $table->index('price', 'idx_pv_price');
        });

        // Add performance indexes to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status', 'idx_orders_status');
            $table->index('created_at', 'idx_orders_created_at');
            $table->index(['customer_id', 'status'], 'idx_orders_customer_status');
            $table->index(['vendor_id', 'status'], 'idx_orders_vendor_status');
        });

        // Add performance indexes to order_details table
        Schema::table('order_details', function (Blueprint $table) {
            $table->index(['order_id', 'product_id'], 'idx_od_order_product');
        });

        // Add performance indexes to product_reviews table
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->index(['product_id', 'is_approved'], 'idx_pr_product_approved');
            $table->index(['customer_id', 'product_id'], 'idx_pr_customer_product');
        });

        // Add performance indexes to wishlists table
        Schema::table('wishlists', function (Blueprint $table) {
            $table->unique(['customer_id', 'product_id'], 'idx_wishlists_cust_prod');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_category_id');
            $table->dropIndex('idx_products_brand_id');
            $table->dropIndex('idx_products_status');
            $table->dropIndex('idx_products_status_category');
            $table->dropIndex('idx_products_status_brand');
            $table->dropIndex('idx_products_status_created');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex('idx_pv_product_primary');
            $table->dropIndex('idx_pv_primary_price');
            $table->dropIndex('idx_pv_price');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_status');
            $table->dropIndex('idx_orders_created_at');
            $table->dropIndex('idx_orders_customer_status');
            $table->dropIndex('idx_orders_vendor_status');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropIndex('idx_od_order_product');
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropIndex('idx_pr_product_approved');
            $table->dropIndex('idx_pr_customer_product');
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropUnique('idx_wishlists_cust_prod');
        });
    }
};
