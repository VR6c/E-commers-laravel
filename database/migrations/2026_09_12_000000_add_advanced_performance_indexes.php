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
        // 1. Composite index on product_images for instant thumbnail lookups
        Schema::table('product_images', function (Blueprint $table) {
            $table->index(['product_id', 'type'], 'idx_pi_product_type');
        });

        // 2. Composite index on categories for parent/sub hierarchy lookups
        Schema::table('categories', function (Blueprint $table) {
            $table->index(['status', 'parent_category_id'], 'idx_categories_status_parent');
        });

        // 3. Covering index on product_reviews for index-only avg rating aggregation
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->index(['product_id', 'is_approved', 'rating'], 'idx_pr_product_approved_rating');
        });

        // 4. Composite index on products for status and name sorting / prefix lookups
        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'name'], 'idx_products_status_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex('idx_pi_product_type');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_status_parent');
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropIndex('idx_pr_product_approved_rating');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_status_name');
        });
    }
};
