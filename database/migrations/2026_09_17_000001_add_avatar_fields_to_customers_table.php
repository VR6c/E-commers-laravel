<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'profile_image')) {
                $table->string('profile_image')->nullable()->after('email');
            }
            if (!Schema::hasColumn('customers', 'avatar_type')) {
                $table->string('avatar_type')->default('default')->after('profile_image'); // 'photo', 'fluttermoji', 'default'
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('customers', 'avatar_type')) {
                $columns[] = 'avatar_type';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
