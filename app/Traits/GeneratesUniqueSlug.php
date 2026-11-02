<?php

namespace App\Traits;

use App\Models\Product;
use Illuminate\Support\Str;

trait GeneratesUniqueSlug
{
    /**
     * Generate a URL slug from $name that is unique in the products table.
     *
     * @param  string  $name  The product name to slugify.
     * @param  int|null  $excludeId  Exclude this product ID from the uniqueness check
     *                               (pass the current product's ID on update so it
     *                               doesn't conflict with itself).
     */
    public function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $count = 1;

        while (
            Product::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base.'-'.$count++;
        }

        return $slug;
    }
}
