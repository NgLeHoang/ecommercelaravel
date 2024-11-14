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
     * Get all coupons with pagination.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAll()
    {
        return $this->model->orderBy('expired_date', 'DESC')->paginate(12);
    }

    /**
     * Find a record by its ID.
     *
     * @param int $id The ID of the record to find.
     * @return \App\Models\Coupon|null
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Store a new coupon in the database.
     *
     * @param array $data
     * @return \App\Models\coupon
     */
    public function storeCoupon(array $data) 
    {
        return $this->model::create($data);
    }

    /**
     * Update an existing coupon.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateCoupon(int $id, array $data): bool
    {
        $coupon = $this->model->find($id);
        return $coupon->update($data);
    }

    /**
     * Delete a coupon by ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteCoupon(int $id): bool
    {
        $coupon = $this->model->find($id);
        return $coupon->delete();
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
