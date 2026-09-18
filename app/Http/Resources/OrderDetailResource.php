<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Services\Store\RecipeAccessService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array for single order detail view.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $shipping = $this->shippingAddress;
        $nameParts = $shipping ? explode(' ', $shipping->name, 2) : [];

        $items = [];
        if ($this->relationLoaded('details')) {
            $items = $this->details->map(function ($detail) {
                $product      = $detail->product;
                $thumbnailUrl = $product?->thumbnail?->image_url
                    ? product_image_url($product->thumbnail->image_url)
                    : null;

                return [
                    'id'                => $detail->id,
                    'product_id'        => $detail->product_id,
                    'product_slug'      => $product?->slug,
                    'product_name'      => $product?->name ?? 'Unknown Product',
                    'product_thumbnail' => $thumbnailUrl,
                    'quantity'          => $detail->quantity,
                    'price'             => (float) $detail->price,
                    'subtotal'          => (float) ($detail->price * $detail->quantity),
                ];
            })->all();
        }

        $receiptUrl = url("/orders/{$this->id}/download-receipt?token=" . app(RecipeAccessService::class)->generateOrderToken($this->resource));

        return [
            'id'              => $this->id,
            'status'          => $this->status,
            'subtotal'        => (float) ($this->total_amount + $this->discount_amount),
            'discount_amount' => (float) $this->discount_amount,
            'coupon_code'     => $this->coupon_code,
            'total'           => (float) $this->total_amount,
            'gateway'         => $this->payment_method ?? 'cod',
            'email'           => $this->guest_email,
            'receipt_url'     => $receiptUrl,
            'shipping'        => $shipping ? [
                'first_name' => $nameParts[0] ?? '',
                'last_name'  => $nameParts[1] ?? '',
                'phone'      => $shipping->phone,
                'address'    => $shipping->address,
                'suite'      => $shipping->suite,
                'city'       => $shipping->city,
                'state'      => $shipping->state,
                'country'    => $shipping->country,
            ] : null,
            'items'           => $items,
            'created_at'      => $this->created_at?->toISOString(),
            'updated_at'      => $this->updated_at?->toISOString(),
        ];
    }
}
