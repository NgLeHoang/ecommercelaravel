<?php

namespace App\Repositories\Eloquent;

use App\Models\Coupon;
use App\Repositories\Contracts\CartRepositoryInterface;
use Carbon\Carbon;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class CartRepository implements CartRepositoryInterface
{
    /**
     * Get all items in the cart.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCartItems()
    {
        return Cart::instance('cart')->content();
    }

    /**
     * Add an item to the cart.
     *
     * @param int $id
     * @param string $name
     * @param int $quantity
     * @param float $price
     * @return void
     */
    public function addToCart(int $id, string $name, int $quantity, float $price): void
    {
        Cart::instance('cart')->add($id, $name, $quantity, $price)->associate('App\Models\Product');
    }

    /**
     * Adjust the quantity of a cart item.
     *
     * @param string $rowId
     * @param string $action ('increase' or 'decrease')
     * @return void
     */
    public function adjustQuantity(string $rowId, string $action): void
    {
        $product = Cart::instance('cart')->get($rowId);
        $qty = ($action === 'increase') ? $product->qty + 1 : max($product->qty - 1, 1);
        Cart::instance('cart')->update($rowId, $qty);
    }

    /**
     * Remove an item from the cart.
     *
     * @param string $rowId
     * @return void
     */
    public function removeItem(string $rowId): void
    {
        Cart::instance('cart')->remove($rowId);
    }

    /**
     * Empty all items from the cart.
     *
     * @return void
     */
    public function emptyCart(): void
    {
        Cart::instance('cart')->destroy();
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
        return Coupon::where('code', $couponCode)
            ->where('expired_date', '>=', Carbon::today()->endOfDay())
            ->where('cart_value', '<=', $cartSubtotal)
            ->first();
    }

    /**
     * Get the subtotal of the cart.
     *
     * @return float
     */
    public function getCartSubtotal()
    {
        return floatval(str_replace(',', '', Cart::instance('cart')->subtotal()));
    }

    /**
     * Get the tax of the cart.
     *
     * @return float
     */
    public function getCartTax()
    {
        return floatval(str_replace(',', '', Cart::instance('cart')->tax()));
    }

    /**
     * Get the total of the cart.
     *
     * @return float
     */
    public function getCartTotal()
    {
        return floatval(str_replace(',', '', Cart::instance('cart')->total()));
    }
}
