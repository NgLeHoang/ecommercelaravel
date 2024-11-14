<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    // Allow code, type and other fields to be mass-assigned
    protected $fillable = ['code', 'type', 'value', 'cart_value', 'expired_date'];
}
