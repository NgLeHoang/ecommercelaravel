<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class WishlistController extends Controller
{
    /**
     * Display all items in the wishlist.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $items = Cart::instance('wishlist')->content();
        return view('wishlist', compact('items'));
    }

    /**
     * Add a product to the wishlist.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addToWishlist(Request $request)
    {
        Cart::instance('wishlist')->add($request->id, $request->name, $request->quantity, $request->price)->associate('App\Models\Product');
        return redirect()->back();
    }

    /**
     * Remove an item from the wishlist by row ID.
     *
     * @param string $rowId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeItem($rowId)
    {
        Cart::instance('wishlist')->remove($rowId);
        return redirect()->back();
    }

    /**
     * Empty the wishlist.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function emptyWishlist()
    {
        Cart::instance('wishlist')->destroy();
        return redirect()->back();
    }

    /**
     * Move an item from the wishlist to the shopping cart.
     *
     * @param string $rowId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function moveToCart($rowId)
    {
        $item = Cart::instance('wishlist')->get($rowId);
        Cart::instance('wishlist')->remove($rowId);
        Cart::instance('cart')->add($item->id, $item->name, $item->qty, $item->price)->associate('App\Models\Product');

        return redirect()->back();
    }
}
