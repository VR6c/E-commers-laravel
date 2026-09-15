<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Recipe extends Model
{
    use HasFactory;

    protected $table = 'recipes';

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'image_url',
        'prep_time',
        'cook_time',
        'servings',
        'difficulty',
        'cuisine',
        'calories',
        'ingredients',
        'instructions',
        'nutritional_info',
        'price',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'ingredients'      => 'array',
        'instructions'     => 'array',
        'nutritional_info' => 'array',
        'is_active'        => 'boolean',
        'is_featured'      => 'boolean',
        'price'            => 'decimal:2',
        'prep_time'        => 'integer',
        'cook_time'        => 'integer',
        'servings'         => 'integer',
        'calories'         => 'integer',
    ];

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_recipe')
            ->withTimestamps();
    }

    // ------------------------------------------------------------------
    // Scopes
    // ------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // ------------------------------------------------------------------
    // Accessors & Helpers
    // ------------------------------------------------------------------

    public function getTotalTimeAttribute(): int
    {
        return ($this->prep_time ?? 0) + ($this->cook_time ?? 0);
    }

    public function getImageSrcAttribute(): string
    {
        if ($this->image_url) {
            if (Str::startsWith($this->image_url, ['http://', 'https://'])) {
                return $this->image_url;
            }
            return asset('storage/' . $this->image_url);
        }

        return 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?w=800&auto=format&fit=crop&q=80';
    }

    public function getDifficultyBadgeClassAttribute(): string
    {
        return match (strtolower($this->difficulty ?? 'medium')) {
            'easy'   => 'badge-easy',
            'medium' => 'badge-medium',
            'hard'   => 'badge-hard',
            default  => 'badge-medium',
        };
    }

    /**
     * Determine whether this recipe is unlocked for the given customer or order.
     */
    public function isUnlockedFor(?Customer $customer = null, ?Order $order = null): bool
    {
        // 1. If user is logged-in admin, always permit access for testing / reviewing
        if (auth()->check()) {
            return true;
        }

        // 2. If completely free with no linked products and zero price
        if ((float) $this->price === 0.0 && $this->products()->count() === 0) {
            return true;
        }

        // 3. If an explicit Order is provided and is completed
        if ($order && in_array(strtolower($order->status), ['completed', 'processing'])) {
            $linkedProductIds = $this->products()->pluck('products.id')->toArray();
            $orderProductIds = $order->details->pluck('product_id')->toArray();

            if (array_intersect($linkedProductIds, $orderProductIds)) {
                return true;
            }
        }

        // 4. Check guest session last_order_id (e.g. on /thank-you page or immediately post-payment)
        $sessionOrderId = session('last_order_id');
        if ($sessionOrderId) {
            $sessionOrder = Order::with('details')->find($sessionOrderId);
            if ($sessionOrder && in_array(strtolower($sessionOrder->status), ['completed', 'processing'])) {
                $linkedProductIds = $this->products()->pluck('products.id')->toArray();
                $orderProductIds = $sessionOrder->details->pluck('product_id')->toArray();
                if (array_intersect($linkedProductIds, $orderProductIds)) {
                    return true;
                }
            }
        }

        // 5. If customer is authenticated, check their historical completed orders
        if ($customer) {
            $linkedProductIds = $this->products()->pluck('products.id')->toArray();
            if (!empty($linkedProductIds)) {
                $hasBought = OrderDetail::whereIn('product_id', $linkedProductIds)
                    ->whereHas('order', function ($q) use ($customer) {
                        $q->where('customer_id', $customer->id)
                          ->whereIn('status', ['completed', 'processing']);
                    })
                    ->exists();

                if ($hasBought) {
                    return true;
                }
            }
        }

        return false;
    }
}
