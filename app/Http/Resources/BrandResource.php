<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'slug'        => $this->slug,
            'logo_url'    => $this->logo_url,
            'status'      => $this->status,
            'name'        => $this->name,
            'description' => $this->description,
        ];
    }
}
