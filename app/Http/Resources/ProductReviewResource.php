<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'rating'       => (int) $this->rating,
            'review'       => $this->review,
            'customer'     => [
                'name'          => $this->customer?->name ?? 'Anonymous',
                'profile_image' => $this->customer?->profile_image ?? null,
            ],
            'created_at'   => $this->created_at?->toISOString(),
        ];
    }
}
