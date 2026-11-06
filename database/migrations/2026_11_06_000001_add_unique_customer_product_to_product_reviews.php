<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     * One review per customer per product, enforced by the database.
     * Runs after 2026_11_06_000000_remove_duplicate_product_reviews.
     */
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->unique(['customer_id', 'product_id'], 'uq_pr_customer_product');
        });

        // The unique index covers the same columns, so the plain index is redundant
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropIndex('idx_pr_customer_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->index(['customer_id', 'product_id'], 'idx_pr_customer_product');
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropUnique('uq_pr_customer_product');
        });
    }
};
