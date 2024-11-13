<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Allow name, slug and other fields to be mass-assigned
    protected $fillable = [
        'name',
        'slug',
        'image',
        'description',
        'short_description',
        'regular_price',
        'sale_price',
        'SKU',
        'status',
        'featured',
        'quantity',
        'images',
        'category_id',
        'brand_id'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
}
