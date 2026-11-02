<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Counts wrong guesses against the current OTP so it can be locked after a few tries.
        if (Schema::hasTable('password_resets') && ! Schema::hasColumn('password_resets', 'attempts')) {
            Schema::table('password_resets', function (Blueprint $table) {
                $table->unsignedTinyInteger('attempts')->default(0);
            });
        }

        // Shared store for the strict route throttle (the default per-request cache can't count across requests).
        if (! Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration');
            });
        }

        if (! Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->integer('expiration');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('password_resets', 'attempts')) {
            Schema::table('password_resets', function (Blueprint $table) {
                $table->dropColumn('attempts');
            });
        }

        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
    }
};
