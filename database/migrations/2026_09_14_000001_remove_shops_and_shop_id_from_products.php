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
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'shop_id')) {
            Schema::table('products', function (Blueprint $table) {
                try {
                    $table->dropForeign(['shop_id']);
                } catch (\Throwable $e) {
                    // Foreign key might have already been removed or named differently
                }
                $table->dropColumn('shop_id');
            });
        }

        Schema::dropIfExists('shops');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('shops')) {
            Schema::create('shops', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->default(1)->constrained('vendors')->onDelete('cascade');
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('logo')->nullable();
                $table->text('description')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'shop_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('shop_id')->nullable()->constrained('shops')->nullOnDelete();
            });
        }
    }
};
