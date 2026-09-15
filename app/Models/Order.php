<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Payment;
use App\Models\ShippingAddress;

class Order extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'orders';

    // Define the fields that can be mass-assigned
    protected $fillable = [
        'vendor_id',
        'customer_id',
        'guest_email',
        'total_amount',
        'coupon_code',
        'discount_amount',
        'status',
        'payment_method',
        'created_at',
        'updated_at',
    ];

    // Define the relationship with the Product model (assuming you have a Product model)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    public function shippingAddress()
    {
        return $this->hasOne(ShippingAddress::class, 'order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Retrieve all active recipes unlocked by products in this order.
     */
    public function unlockedRecipes()
    {
        $productIds = $this->details->pluck('product_id')->filter()->unique()->toArray();
        if (empty($productIds)) {
            return collect();
        }

        return Recipe::whereHas('products', function ($q) use ($productIds) {
            $q->whereIn('products.id', $productIds);
        })->where('is_active', true)->get();
    }
}
