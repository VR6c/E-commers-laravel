<?php

namespace App\Traits;

use App\Models\Product;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait SyncsProductVariants
{
    /**
     * Create variants and their attribute-value pivot rows for a product.
     *
     * @param  array<int, array<string, mixed>>  $variants
     */
    public function syncVariants(array $variants, Product $product): void
    {
        foreach ($variants as $variantData) {
            $variant = $product->variants()->create([
                'variant_slug'   => Str::slug($variantData['name']) . '-' . uniqid(),
                'name'           => $variantData['name'],
                'price'          => $variantData['price'],
                'discount_price' => $variantData['discount_price'] ?? null,
                'stock'          => $variantData['stock'],
                'SKU'            => $variantData['SKU'],
                'barcode'        => $variantData['barcode'] ?? null,
                'weight'         => $variantData['weight'] ?? null,
                'dimensions'     => $variantData['dimensions'] ?? null,
                'is_primary'     => true,
            ]);

            foreach (['size_id', 'color_id'] as $attrType) {
                if (! empty($variantData[$attrType])) {
                    DB::table('product_variant_attribute_values')->insert([
                        'product_id'         => $product->id,
                        'product_variant_id' => $variant->id,
                        'attribute_value_id' => $variantData[$attrType],
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]);
                    ProductAttributeValue::firstOrCreate([
                        'product_id'         => $product->id,
                        'attribute_value_id' => $variantData[$attrType],
                    ]);
                }
            }
        }
    }
}
