<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Services\Store\RecipeAccessService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array for list views.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $shipping = $this->shippingAddress;
        $nameParts = $shipping ? explode(' ', $shipping->name, 2) : [];
        $firstName = $nameParts[0] ?? '';
        $lastName  = $nameParts[1] ?? '';

        $items = [];
        if ($this->relationLoaded('details')) {
            $items = $this->details->map(function ($detail) {
                $product = $detail->product;
                $thumbnailUrl = $product?->thumbnail?->image_url
                    ? product_image_url($product->thumbnail->image_url)
                    : ($product?->image_url ? product_image_url($product->image_url) : null);

                return [
                    'id'                => $detail->id,
                    'product_id'        => $detail->product_id,
                    'product_name'      => $product?->name ?? 'Unknown Product',
                    'product_thumbnail' => $thumbnailUrl,
                    'quantity'          => $detail->quantity,
                    'price'             => (float) $detail->price,
                ];
            })->all();
        }

        $receiptUrl = url("/orders/{$this->id}/download-receipt?token=" . app(RecipeAccessService::class)->generateOrderToken($this->resource));

        return [
            'id'              => $this->id,
            'status'          => $this->status,
            'total'           => (float) $this->total_amount,
            'coupon_code'     => $this->coupon_code,
            'discount_amount' => (float) $this->discount_amount,
            'first_name'      => $firstName,
            'last_name'       => $lastName,
            'phone'           => $shipping?->phone,
            'email'           => $this->guest_email,
            'receipt_url'     => $receiptUrl,
            'address'         => $shipping?->address,
            'city'            => $shipping?->city,
            'country'         => $shipping?->country,
            'gateway'         => $this->payment_method ?? 'cod',
            'items'           => $items,
            'created_at'      => $this->created_at?->toISOString(),
        ];
    }
}
