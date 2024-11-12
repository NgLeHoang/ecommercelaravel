<?php

namespace App\Repositories\Eloquent;

use App\Models\Coupon;
use App\Repositories\Contracts\CouponRepositoryInterface;
use Carbon\Carbon;

class CouponRepository implements CouponRepositoryInterface
{
    /**
     * The model instance that this repository will interact with.
     *
     * @var \App\Models\Coupon
     */
    protected $model;

    /**
     * CouponRepository constructor.
     *
     * @param \App\Models\Coupon $model
     */
    public function __construct(Coupon $model)
    {
        $this->model = $model;
    }

    /**
     * Validate the provided coupon code.
     *
     * @param string $couponCode
     * @param float $cartSubtotal
     * @return \App\Models\Coupon|null
     */
    public function validateCoupon(string $couponCode, float $cartSubtotal): ?Coupon
    {
        return $this->model::where('code', $couponCode)
            ->where('expired_date', '>=', Carbon::today()->endOfDay())
            ->where('cart_value', '<=', $cartSubtotal)
            ->first();
    }
}
