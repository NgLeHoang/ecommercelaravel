<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class CartController extends Controller
{
    public function index()
    {
        $items = Cart::instance('cart')->content();
        return view('cart', compact('items'));
    }

    public function addToCart(Request $request)
    {
        Cart::instance('cart')->add($request->id, $request->name, $request->quantity, $request->price)->associate('App\Models\Product');
        return redirect()->back();
    }

    public function adjustCartQuantity($rowId, $action)
    {
        $product = Cart::instance('cart')->get($rowId);
        $qty = ($action === 'increase') ? $product->qty + 1 : $product->qty - 1;
        Cart::instance('cart')->update($rowId, $qty);

        return redirect()->back();
    }

    //Remove one item in cart
    public function removeItem($rowId)
    {
        Cart::instance('cart')->remove($rowId);
        return redirect()->back();
    }

    //Remove all item in cart
    public function emptyCart()
    {
        Cart::instance('cart')->destroy();
        return redirect()->back();
    }

    public function applyCouponCode(Request $request)
    {
        $coupon_code = $request->coupon_code;

        //Check coupon
        if (!isset($coupon_code)) {
            return redirect()->back()->with('error', 'Invalid coupon code!');
        }

        $coupon = Coupon::where('code', $coupon_code)
            ->where('expired_date', '>=', Carbon::today()->endOfDay())
            ->where('cart_value', '<=', floatval(str_replace(',', '', Cart::instance('cart')->subtotal())))
            ->first();

        if (!$coupon) {
            return redirect()->back()->with('error', 'Invalid coupon code!');
        }

        Session::put('coupon', [
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'cart_value' => $coupon->cart_value
        ]);

        $this->calculateDiscount();

        return redirect()->back()->with('success', 'Coupon has been applied!');
    }

    public function calculateDiscount()
    {
        $discount = 0;
        if (Session::has('coupon')) {
            $coupon = Session::get('coupon');
            $cartSubtotal = floatval(str_replace(',', '', Cart::instance('cart')->subtotal()));

            $discount = $coupon['type'] === 'fixed' ? $coupon['value'] : ($cartSubtotal * $coupon['value']) / 100;

            $subtotalAfterDiscount = $cartSubtotal - $discount;
            $taxAfterDiscount = ($subtotalAfterDiscount * config('cart.tax')) / 100;
            $totalAfterDiscount = $subtotalAfterDiscount + $taxAfterDiscount;

            Session::put('discounts', [
                'discount' => number_format($discount, 2, '.', ''),
                'subtotal' => number_format($subtotalAfterDiscount, 2, '.', ''),
                'tax' => number_format($taxAfterDiscount, 2, '.', ''),
                'total' => number_format($totalAfterDiscount, 2, '.', ''),
            ]);
        }
    }

    public function removeCouponCode()
    {
        Session::forget('coupon');
        Session::forget('discounts');

        return redirect()->back()->with('success', 'Coupon has been removed!');
    }

    public function checkout()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $address = Address::where('user_id', Auth::user()->id)->where('is_default', 1)->first();

        return view('checkout', compact('address'));
    }

    public function placeAnOrder(Request $request)
    {
        $user_id = Auth::user()->id;
        $address = Address::where('user_id', $user_id)->where('is_default', true)->first();

        //Create address if user not create address before
        if (!$address) {
            $request->validate([
                'name' => 'required|max:100',
                'phone' => 'required|numeric|digits:10',
                'district' => 'required',
                'city' => 'required',
                'address' => 'required',
                'locality' => 'required'
            ]);

            //Init address
            $address = new Address();
            $address->name = $request->name;
            $address->phone = $request->phone;
            $address->district = $request->district;
            $address->city = $request->city;
            $address->address = $request->address;
            $address->locality = $request->locality;
            $address->country = 'Vietnam';
            $address->user_id = $user_id;
            $address->is_default = true;

            $address->save();
        }

        $this->setAmountForCheckout();

        $order = new Order();
        $order->user_id = $user_id;
        $order->subtotal = Session::get('checkout')['subtotal'];
        $order->discount = Session::get('checkout')['discount'];
        $order->tax = Session::get('checkout')['tax'];
        $order->total = Session::get('checkout')['total'];
        $order->name = $address->name;
        $order->phone = $address->phone;
        $order->locality = $address->locality;
        $order->address = $address->address;
        $order->city = $address->city;
        $order->district = $address->district;
        $order->country = $address->country;

        $order->save();

        foreach (Cart::instance('cart')->content() as $item) {
            $orderItem = new OrderItem();
            $orderItem->product_id = $item->id;
            $orderItem->order_id = $order->id;
            $orderItem->price = $item->price;
            $orderItem->quantity = $item->qty;

            $orderItem->save();
        }
        if ($request->mode == 'card') {
            //Function update...
        } elseif ($request->mode == 'paypal') {
            //Function update...
        } elseif ($request->mode == 'cod') {
            $transaction = new Transaction();
            $transaction->user_id = $user_id;
            $transaction->order_id = $order->id;
            $transaction->mode = $request->mode;
            $transaction->status = 'pending';

            $transaction->save();
        }

        Cart::instance('cart')->destroy();
        Session::forget('checkout');
        Session::forget('coupon');
        Session::forget('discounts');
        Session::put('order_id', $order->id);
        return redirect()->route('cart.order.confirm');
    }

    public function setAmountForCheckout()
    {
        if (!Cart::instance('cart')->content()->count() > 0) {
            Session::forget('checkout');
            return;
        }

        if (Session::has('coupon')) {
            Session::put('checkout', [
                'discount' => floatval(str_replace(',', '', Session::get('discounts')['discount'])),
                'subtotal' => floatval(str_replace(',', '', Session::get('discounts')['subtotal'])),
                'tax' => floatval(str_replace(',', '', Session::get('discounts')['tax'])),
                'total' => floatval(str_replace(',', '', Session::get('discounts')['total']))
            ]);
        } else {
            $subtotal = floatval(str_replace(',', '', Cart::instance('cart')->subtotal()));
            $tax = floatval(str_replace(',', '', Cart::instance('cart')->tax()));
            $total = floatval(str_replace(',', '', Cart::instance('cart')->total()));

            Session::put('checkout', [
                'discount' => 0,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total
            ]);
        }
    }

    public function orderConfirmation()
    {
        if (Session::has('order_id')) {
            $order = Order::find(Session::get('order_id'));
            return view('order-confirm', compact('order'));
        }
        return redirect()->route('cart.index');
    }
}
