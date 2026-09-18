<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Recipe;
use Illuminate\Support\Facades\Cache;

class RecipeObserver
{
    /**
     * Handle the Recipe "saved" event.
     */
    public function saved(Recipe $recipe): void
    {
        Cache::forget('recipe_distinct_cuisines');
    }

    /**
     * Handle the Recipe "deleted" event.
     */
    public function deleted(Recipe $recipe): void
    {
        Cache::forget('recipe_distinct_cuisines');
    }
}
