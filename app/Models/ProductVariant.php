<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variant_slug',
        'name',
        'price',
        'discount_price',
        'stock',
        'SKU',
        'barcode',
        'is_primary',
        'weight',
        'dimensions',
    ];

    protected $casts = [
        'is_primary'     => 'boolean',
        'price'          => 'decimal:2',
        'discount_price' => 'decimal:2',
        'stock'          => 'integer',
    ];

    protected $appends = ['converted_price', 'converted_discount_price'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'variant_id');
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attribute_values');
    }

    public function getConvertedPriceAttribute()
    {
        return convert_price($this->price);
    }

    public function getConvertedDiscountPriceAttribute()
    {
        return $this->discount_price ? convert_price($this->discount_price) : null;
    }
}
