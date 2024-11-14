<?php

namespace App\Repositories\Contracts;

interface CouponRepositoryInterface
{
    /**
     * Get all coupons with pagination.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAll();

    /**
     * Find a record by its ID.
     *
     * @param int $id The ID of the record to find.
     * @return \App\Models\Coupon|null
     */
    public function find($id);

        /**
     * Store a new coupon in the database.
     *
     * @param array $data
     * @return \App\Models\Coupon
     */
    public function storeCoupon(array $data);

    /**
     * Update an existing coupon.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateCoupon(int $id, array $data): bool;

    /**
     * Delete a coupon by ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteCoupon(int $id): bool;

    /**
     * Validate a coupon code based on the code and cart subtotal.
     *
     * @param string $couponCode
     * @param float $cartSubtotal
     * @return \App\Models\Coupon|null
     */
    public function validateCoupon(string $couponCode, float $cartSubtotal): ?\App\Models\Coupon;
}
