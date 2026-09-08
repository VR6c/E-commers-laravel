<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        // brands — add name, description
        Schema::table('brands', function (Blueprint $table) {
            $table->string('name')->nullable()->after('slug');
            $table->text('description')->nullable()->after('name');
        });

        // banners — add description, image_url
        Schema::table('banners', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->string('image_url')->nullable()->after('description');
        });

        // pages — add title, content, image_url
        Schema::table('pages', function (Blueprint $table) {
            $table->string('title')->nullable()->after('slug');
            $table->longText('content')->nullable()->after('title');
            $table->string('image_url')->nullable()->after('content');
        });

        // menu_items — add title
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('title')->nullable()->after('menu_id');
        });

        // social_media_links — add name
        Schema::table('social_media_links', function (Blueprint $table) {
            $table->string('name')->nullable()->after('link');
        });

        // products — add name, description, short_description, tags
        Schema::table('products', function (Blueprint $table) {
            $table->string('name')->nullable()->after('slug');
            $table->longText('description')->nullable()->after('name');
            $table->text('short_description')->nullable()->after('description');
            $table->string('tags')->nullable()->after('short_description');
        });

        // product_variants — add name
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('name')->nullable()->after('variant_slug');
        });
    }

    public function down(): void
    {
        Schema::table('brands',          fn ($t) => $t->dropColumn(['name', 'description']));
        Schema::table('banners',         fn ($t) => $t->dropColumn(['description', 'image_url']));
        Schema::table('pages',           fn ($t) => $t->dropColumn(['title', 'content', 'image_url']));
        Schema::table('menu_items',      fn ($t) => $t->dropColumn('title'));
        Schema::table('social_media_links', fn ($t) => $t->dropColumn('name'));
        Schema::table('products',        fn ($t) => $t->dropColumn(['name', 'description', 'short_description', 'tags']));
        Schema::table('product_variants', fn ($t) => $t->dropColumn('name'));
    }
};
