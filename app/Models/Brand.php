<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    // Allow name, slug and image fields to be mass-assigned
    protected $fillable = ['name', 'slug', 'image'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
