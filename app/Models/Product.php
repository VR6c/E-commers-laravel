<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'brand_id', 'seller_id', 'shop_id', 'price', 'stock',
        'status', 'slug', 'currency', 'SKU', 'weight', 'dimensions',
        'product_type', 'image_url', 'vendor_id',
        'name', 'description', 'short_description', 'tags',
    ];

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function thumbnail()
    {
        return $this->hasOne(ProductImage::class)->where('type', 'thumb');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class)->approved()->latest();
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function primaryVariant()
    {
        return $this->hasOne(ProductVariant::class)->whereRaw('is_primary is true');
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values')
            ->with('attribute');
    }

    public function wishlistedBy()
    {
        return $this->belongsToMany(Customer::class, 'wishlists');
    }

    // ------------------------------------------------------------------
    // Accessors / helpers
    // ------------------------------------------------------------------

    public function averageRating()
    {
        return $this->reviews()->avg('rating') ?: 0;
    }

    public function getPriceAttribute()
    {
        return $this->primaryVariant?->price ?? $this->variants->firstWhere('is_primary', true)?->price ?? $this->variants->first()?->price ?? 0;
    }

    public function getDiscountPriceAttribute()
    {
        return $this->primaryVariant?->discount_price ?? $this->variants->firstWhere('is_primary', true)?->discount_price ?? $this->variants->first()?->discount_price ?? null;
    }

    public function getConvertedPriceAttribute()
    {
        return convert_price($this->price);
    }

    public function getConvertedDiscountPriceAttribute()
    {
        return $this->discount_price ? convert_price($this->discount_price) : null;
    }

    /**
     * Compatibility shim — controllers/views calling $product->getTranslation('name', 'en')
     * now get the direct column value.
     */
    public function getTranslation(string $field, string $locale = 'en'): ?string
    {
        return $this->$field ?? null;
    }

    /**
     * Compatibility shim — $product->translation->name
     */
    public function getTranslationAttribute()
    {
        return $this;
    }
}
