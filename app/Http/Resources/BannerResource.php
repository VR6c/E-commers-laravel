<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrl = null;
        if (! empty($this->image_url)) {
            $imageUrl = Str::startsWith($this->image_url, ['http://', 'https://'])
                ? $this->image_url
                : Storage::disk('public')->url($this->image_url);
        }

        return [
            'id'          => $this->id,
            'type'        => $this->type,
            'title'       => $this->title,
            'description' => $this->description,
            'image_url'   => $imageUrl,
        ];
    }
}
