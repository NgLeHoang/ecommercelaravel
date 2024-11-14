<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    // Allow tagline, title and other fields to be mass-assigned
    protected $fillable = ['tagline', 'title', 'subtitle', 'link', 'image', 'status'];
}
