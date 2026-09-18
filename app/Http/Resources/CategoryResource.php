<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $childrenCount = $this->relationLoaded('children')
            ? $this->children->sum('products_count')
            : 0;

        $totalProducts = (int) ($this->products_count ?? 0) + $childrenCount;

        $children = [];
        if ($this->relationLoaded('children')) {
            $children = $this->children
                ->filter(fn ($c) => ($c->products_count ?? 0) > 0)
                ->values()
                ->map(fn ($child) => [
                    'id'             => $child->id,
                    'slug'           => $child->slug,
                    'name'           => $child->name,
                    'description'    => $child->description,
                    'image_url'      => $child->image_url,
                    'products_count' => (int) ($child->products_count ?? 0),
                ])
                ->all();
        }

        return [
            'id'             => $this->id,
            'slug'           => $this->slug,
            'name'           => $this->name,
            'description'    => $this->description,
            'image_url'      => $this->image_url,
            'products_count' => $totalProducts,
            'children'       => $children,
        ];
    }
}
