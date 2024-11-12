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
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\CouponRepositoryInterface;
use App\Repositories\Contracts\AddressRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;

class CartController extends Controller
{
    /**
     * Repository for handling cart data operations.
     *
     * @var \App\Repositories\Contracts\CartRepositoryInterface
     */
    protected $cartRepo;

    /**
     * Repository for handling coupon data operations.
     *
     * @var \App\Repositories\Contracts\CouponRepositoryInterface
     */
    protected $couponRepo;

    /**
     * Repository for handling address data operations.
     *
     * @var \App\Repositories\Contracts\AddressRepositoryInterface
     */
    protected $addressRepo;

    /**
     * Repository for handling order data operations.
     *
     * @var \App\Repositories\Contracts\OrderRepositoryInterface
     */
    protected $orderRepo;

    /**
     * Repository for handling order item data operations.
     *
     * @var \App\Repositories\Contracts\OrderItemRepositoryInterface
     */
    protected $orderItemRepo;

    /**
     * Repository for handling transaction data operations.
     *
     * @var \App\Repositories\Contracts\TransactionRepositoryInterface
     */
    protected $transactionRepo;

    /**
     * Create a new controller instance and inject dependencies.
     *
     * @param \App\Repositories\Contracts\CartRepositoryInterface $cartRepo
     * @param \App\Repositories\Contracts\CouponRepositoryInterface $couponRepo
     * @param \App\Repositories\Contracts\AddressRepositoryInterface $addressRepo
     * @param \App\Repositories\Contracts\OrderRepositoryInterface $orderRepo
     * @param \App\Repositories\Contracts\OrderItemRepositoryInterface $orderItemRepo
     * @param \App\Repositories\Contracts\TransactionRepositoryInterface $transactionRepo
     */
    public function __construct(
        CartRepositoryInterface $cartRepo,
        CouponRepositoryInterface $couponRepo,
        AddressRepositoryInterface $addressRepo,
        OrderRepositoryInterface $orderRepo,
        OrderItemRepositoryInterface $orderItemRepo,
        TransactionRepositoryInterface $transactionRepo
    ) {
        $this->cartRepo = $cartRepo;
        $this->couponRepo = $couponRepo;
        $this->addressRepo = $addressRepo;
        $this->orderRepo = $orderRepo;
        $this->orderItemRepo = $orderItemRepo;
        $this->transactionRepo = $transactionRepo;
    }

    /**
     * Display the cart items for guest.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $items = $this->cartRepo->getCartItems();
        return view('cart', compact('items'));
    }

    /**
     * Add an item to the shopping cart.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addToCart(Request $request)
    {
        $this->cartRepo->addToCart($request->id, $request->name, $request->quantity, $request->price);
        return redirect()->back();
    }

    /**
     * Adjust the quantity of a specific item in the cart.
     *
     * @param string $rowId
     * @param string $action ('increase' or 'decrease')
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adjustCartQuantity($rowId, $action)
    {
        $this->cartRepo->adjustQuantity($rowId, $action);
        return redirect()->back();
    }

    /**
     * Remove an item from the shopping cart.
     *
     * @param string $rowId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeItem($rowId)
    {
        $this->cartRepo->removeItem($rowId);
        return redirect()->back();
    }

    /**
     * Empty all items from the shopping cart.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function emptyCart()
    {
        $this->cartRepo->emptyCart();
        return redirect()->back();
    }

    /**
     * Apply a coupon code to the current cart.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function applyCouponCode(Request $request)
    {
        $coupon_code = $request->coupon_code;

        // Check if the coupon code is provided
        if (!isset($coupon_code)) {
            return redirect()->back()->with('error', 'Invalid coupon code!');
        }

        // Get cart subtotal
        $cart_subtotal = $this->cartRepo->getCartSubtotal();

        // Validate the coupon using the repository
        $coupon = $this->couponRepo->validateCoupon($coupon_code, $cart_subtotal);

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

    /**
     * Calculate and apply discount to the current cart based on the coupon.
     *
     * @return void
     */
    public function calculateDiscount()
    {
        $discount = 0;
        if (Session::has('coupon')) {
            $coupon = Session::get('coupon');
            $cartSubtotal = $this->cartRepo->getCartSubtotal();

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

    /**
     * Remove the applied coupon code and any associated discount from the session.
     *
     * This method clears the 'coupon' and 'discounts' data from the session, effectively
     * removing any applied coupon and recalculating the cart total without a discount.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeCouponCode()
    {
        Session::forget('coupon');
        Session::forget('discounts');

        return redirect()->back()->with('success', 'Coupon has been removed!');
    }

    /**
     * Display the checkout page with the user's default address.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function checkout()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $address = $this->addressRepo->findByUserId(Auth::user()->id);

        return view('checkout', compact('address'));
    }

    /**
     * Process an order for the authenticated user, creating an address if none exists.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function placeAnOrder(Request $request)
    {
        $userId  = Auth::id();
        $address = $this->addressRepo->findByUserId($userId);

        //Checks if an address exists for the user
        if (!$address) {
            $validatedData = $request->validate([
                'name' => 'required|max:100',
                'phone' => 'required|numeric|digits:10',
                'district' => 'required',
                'city' => 'required',
                'address' => 'required',
                'locality' => 'required'
            ]);

            $validatedData['country'] = 'Vietnam';
            $validatedData['is_default'] = true;
            $this->addressRepo->storeAddress($validatedData);
            $address = $this->addressRepo->findByUserId($userId);
        }

        $this->setAmountForCheckout();

        //Create an order
        $orderData = [
            'user_id' => $userId,
            'subtotal' => Session::get('checkout')['subtotal'],
            'discount' => Session::get('checkout')['discount'],
            'tax' => Session::get('checkout')['tax'],
            'total' => Session::get('checkout')['total'],
            'name' => $address->name,
            'phone' => $address->phone,
            'locality' => $address->locality,
            'address' => $address->address,
            'city' => $address->city,
            'district' => $address->district,
            'country' => $address->country,
        ];

        $order = $this->orderRepo->createOrder($orderData);
        
        //Create order items
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
            //Create transaction
            $transactionData = [
                'user_id' => $userId,
                'order_id' => $order->id,
                'mode' => $request->mode,
                'status' => 'pending',
            ];
            $this->transactionRepo->createTransaction($transactionData);
        }

        $this->cartRepo->emptyCart();
        Session::forget('checkout');
        Session::forget('coupon');
        Session::forget('discounts');
        Session::put('order_id', $order->id);
        return redirect()->route('cart.order.confirm');
    }

    /**
     * Set the checkout amounts and store them in the session.
     * 
     * @return void
     */
    public function setAmountForCheckout()
    {
        // If there are no items in the cart, clear the checkout session.
        if (!$this->cartRepo->getCartItems->count() > 0) {
            Session::forget('checkout');
            return;
        }

        // If a coupon is applied, use the discount values from the session.
        if (Session::has('coupon')) {
            Session::put('checkout', [
                'discount' => floatval(str_replace(',', '', Session::get('discounts')['discount'])),
                'subtotal' => floatval(str_replace(',', '', Session::get('discounts')['subtotal'])),
                'tax' => floatval(str_replace(',', '', Session::get('discounts')['tax'])),
                'total' => floatval(str_replace(',', '', Session::get('discounts')['total']))
            ]);
        } else {
            // If no coupon is applied, calculate the amounts from the cart.
            $subtotal = $this->cartRepo->getCartSubtotal();
            $tax = $this->cartRepo->getCartTax();
            $total = $this->cartRepo->getCartTotal();

            // Store the amounts in the session.
            Session::put('checkout', [
                'discount' => 0,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total
            ]);
        }
    }

    /**
     * Show the order confirmation page.
     *
     * @return \Illuminate\Http\Response
     */
    public function orderConfirmation()
    {
        // Check if 'order_id' exists in the session
        if (Session::has('order_id')) {
            $order = $this->orderRepo->find(Session::get('order_id'));

            return view('order-confirm', compact('order'));
        }

        // If no order is found or no order ID in the session, redirect to the cart page.
        return redirect()->route('cart.index');
    }
}
