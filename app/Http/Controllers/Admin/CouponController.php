<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CouponRepositoryInterface;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Repository for handling coupon data operations.
     *
     * @var \App\Repositories\Eloquent\CouponRepositoryInterface
     */
    protected $couponRepo;

    /**
     * Create a new controller instance and inject dependencies.
     *
     * @param \App\Repositories\Eloquent\CouponRepositoryInterface $couponRepo
     */
    public function __construct(CouponRepositoryInterface $couponRepo)
    {
        $this->couponRepo = $couponRepo;
    }

    /**
     * Display a listing of all coupons in the admin view.
     *
     * @return \Illuminate\View\View
     */
    public function coupons()
    {
        $coupons = $this->couponRepo->getAll();
        return view('admin.coupons', compact('coupons'));
    }

    /**
     * Display a page add coupon view.
     *
     * @return \Illuminate\View\View
     */
    public function addCoupon()
    {
        return view('admin.coupon-add');
    }

    /**
     * Store a new coupon.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expired_date' => 'required|date'
        ]);

        // Prepare data for store
        $couponData = $request->only([
            'code', 'type', 'value', 'cart_value', 'expired_date'
        ]);

        // Store data
        $this->couponRepo->storeCoupon($couponData);

        // Redirect to page display all coupon 
        return redirect()->route('admin.coupons.index')->with('status', 'Coupon has added sucessfully');
    }

    /**
     * Display a page edit coupon view.
     *
     * @return \Illuminate\View\View
     */
    public function editCoupon($id)
    {
        $coupon = $this->couponRepo->find($id);
        return view('admin.coupon-edit', compact('coupon'));
    }

    /**
     * Update an existing coupon.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expired_date' => 'required|date',
        ]);

        $couponData = $request->only([
            'code', 'type', 'value', 'cart_value', 'expired_date'
        ]);

        $this->couponRepo->updateCoupon($request->id, $couponData);

        return redirect()->route('admin.coupons.index')->with('status', 'Coupon has updated successfully');
    }

    /**
     * Delete a coupon by id.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteCoupon($id)
    {
        $this->couponRepo->deleteCoupon($id);

        return redirect()->route('admin.coupons.index')->with('status', 'Coupon has deleted successfully');
    }
}