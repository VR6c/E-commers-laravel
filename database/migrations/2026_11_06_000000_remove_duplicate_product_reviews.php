<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Keep only the earliest review (lowest id) per customer + product.
     */
    public function up(): void
    {
        $duplicates = DB::table('product_reviews')
            ->select('customer_id', 'product_id', DB::raw('MIN(id) as keep_id'))
            ->groupBy('customer_id', 'product_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $group) {
            DB::table('product_reviews')
                ->where('customer_id', $group->customer_id)
                ->where('product_id', $group->product_id)
                ->where('id', '!=', $group->keep_id)
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     * Deleted reviews cannot be restored.
     */
    public function down(): void
    {
        //
    }
};
