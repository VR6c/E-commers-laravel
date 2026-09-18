<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    protected bool $full = false;

    /**
     * Create a new resource instance.
     */
    public function __construct($resource, bool $full = false)
    {
        parent::__construct($resource);
        $this->full = $full;
    }

    /**
     * Set full detail mode.
     */
    public function withFullDetails(bool $full = true): self
    {
        $this->full = $full;
        return $this;
    }

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

        $variants = [];
        if ($this->relationLoaded('variants')) {
            $variants = ProductVariantResource::collection($this->variants)->resolve();
        }

        $data = [
            'id'                => $this->id,
            'slug'              => $this->slug,
            'name'              => $this->name,
            'short_description' => $this->short_description,
            'price'             => $this->getConvertedPriceAttribute(),
            'thumbnail'         => $thumbnailUrl,
            'category'          => $this->category?->name ?? '',
            'category_id'       => $this->category_id,
            'brand'             => $this->brand?->name ?? null,
            'brand_id'          => $this->brand_id,
            'rating'            => round((float) ($this->reviews_avg_rating ?? 0), 1),
            'reviews_count'     => (int) ($this->reviews_count ?? 0),
            'variants'          => $variants,
        ];

        if ($this->full) {
            $gallery = [];
            if ($this->relationLoaded('images')) {
                $gallery = $this->images
                    ->where('type', '!=', 'thumb')
                    ->map(fn ($img) => product_image_url($img->image_url))
                    ->values()
                    ->all();
            }

            $data['description'] = $this->description;
            $data['gallery']     = $gallery;
            $data['tags']        = $this->tags;
            $data['weight']      = $this->weight;
            $data['sku']         = $this->SKU;
        }

        return $data;
    }
}
