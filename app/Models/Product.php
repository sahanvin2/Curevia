<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'image', 'price', 'original_price',
        'rating', 'reviews_count', 'badge', 'category', 'long_description',
        'features', 'specifications', 'gallery', 'affiliate_url', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
        'features' => 'array',
        'specifications' => 'array',
        'gallery' => 'array',
    ];
}
