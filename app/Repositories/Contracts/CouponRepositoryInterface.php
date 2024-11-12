<?php

namespace App\Repositories\Contracts;

interface CouponRepositoryInterface
{
    /**
     * Validate a coupon code based on the code and cart subtotal.
     *
     * @param string $couponCode
     * @param float $cartSubtotal
     * @return \App\Models\Coupon|null
     */
    public function validateCoupon(string $couponCode, float $cartSubtotal): ?\App\Models\Coupon;
}
