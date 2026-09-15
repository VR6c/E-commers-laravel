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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('image_url')->nullable();
            $table->unsignedInteger('prep_time')->nullable()->comment('Prep time in minutes');
            $table->unsignedInteger('cook_time')->nullable()->comment('Cook time in minutes');
            $table->unsignedInteger('servings')->default(2);
            $table->string('difficulty')->default('medium'); // easy, medium, hard
            $table->string('cuisine')->nullable(); // e.g. Italian, Asian, Bakery, Healthy
            $table->unsignedInteger('calories')->nullable();
            $table->json('ingredients')->nullable(); // [{item, quantity, unit, product_id}]
            $table->json('instructions')->nullable(); // [{step, title, description}]
            $table->json('nutritional_info')->nullable(); // {calories, protein, carbs, fat, fiber}
            $table->decimal('price', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index('slug');
            $table->index('is_active');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
