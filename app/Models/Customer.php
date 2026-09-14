<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'status',
        'profile_image',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishlistProducts()
    {
        return $this->belongsToMany(Product::class, 'wishlists', 'customer_id', 'product_id')
            ->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->profile_image) {
            if (\Illuminate\Support\Str::startsWith($this->profile_image, ['http://', 'https://'])) {
                return $this->profile_image;
            }
            return asset('storage/' . $this->profile_image);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff&size=80';
    }
}
