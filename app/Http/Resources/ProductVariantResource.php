<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attributes = [];
        if ($this->relationLoaded('attributeValues')) {
            $attributes = $this->attributeValues->map(fn ($av) => [
                'id'    => $av->id,
                'name'  => $av->attribute?->name ?? '',
                'value' => $av->value,
            ])->values()->all();
        }

        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'price'          => $this->converted_price,
            'discount_price' => $this->converted_discount_price,
            'stock'          => $this->stock,
            'sku'            => $this->SKU,
            'is_primary'     => (bool) $this->is_primary,
            'attributes'     => $attributes,
        ];
    }
}
