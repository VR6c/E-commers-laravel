<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSuggestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $thumbnailUrl = null;
        if ($this->relationLoaded('thumbnail') && $this->thumbnail) {
            $thumbnailUrl = product_image_url($this->thumbnail->image_url);
        } elseif ($this->thumbnail) {
            $thumbnailUrl = product_image_url($this->thumbnail->image_url);
        } elseif ($this->image_url) {
            $thumbnailUrl = product_image_url($this->image_url);
        }

        return [
            'id'             => $this->id,
            'slug'           => $this->slug,
            'name'           => $this->name,
            'price'          => $this->getConvertedPriceAttribute(),
            'discount_price' => $this->getConvertedDiscountPriceAttribute(),
            'thumbnail'      => $thumbnailUrl,
            'category'       => $this->category?->name ?? '',
            'category_id'    => $this->category_id,
            'brand'          => $this->brand?->name ?? null,
            'brand_id'       => $this->brand_id,
            'rating'         => round((float) ($this->reviews_avg_rating ?? 0), 1),
            'reviews_count'  => (int) ($this->reviews_count ?? 0),
        ];
    }
}
