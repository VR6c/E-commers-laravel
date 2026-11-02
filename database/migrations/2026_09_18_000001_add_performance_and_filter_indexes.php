<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Not transactional, so each step skips indexes that already exist and the migration can be re-run safely.
    public $withinTransaction = false;

    /**
     * Indexes added by this migration: table => [index name => columns].
     * (coupons.code is already unique, and coupons has no status column, so it needs no extra index.)
     */
    private function indexes(): array
    {
        return [
            // Order lookup by product (e.g. recipe unlock checks)
            'order_details' => ['idx_od_product_id' => ['product_id']],
            // Quick product-attribute lookups
            'product_attribute_values' => ['idx_pav_product_attribute' => ['product_id', 'attribute_value_id']],
            // Reverse lookup in shop filter
            'product_variant_attribute_values' => ['idx_pvav_attr_val_variant' => ['attribute_value_id', 'product_variant_id']],
            // Catalog filtering
            'recipes' => [
                'idx_recipes_active_cuisine' => ['is_active', 'cuisine'],
                'idx_recipes_active_difficulty' => ['is_active', 'difficulty'],
            ],
        ];
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->indexes() as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($indexes as $name => $columns) {
                if (Schema::hasIndex($table, $name) || ! Schema::hasColumns($table, $columns)) {
                    continue;
                }

                Schema::table($table, fn (Blueprint $t) => $t->index($columns, $name));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->indexes() as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach (array_keys($indexes) as $name) {
                if (Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($name));
                }
            }
        }
    }
};
