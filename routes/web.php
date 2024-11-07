<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home.index');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product_slug}', [ShopController::class, 'product_details'])->name('shop.product.details');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add_to_cart'])->name('cart.add');
Route::put('/cart/adjust-quantity/{rowId}/{action}', [CartController::class, 'adjust_cart_quantity'])->name('cart.quantity.adjust');
Route::delete('/cart/remove/{rowId}', [CartController::class, 'remove_item'])->name('cart.item.remove');
Route::delete('/cart/clear', [CartController::class, 'empty_cart'])->name('cart.clear');
Route::post('/cart/apply-coupon', [CartController::class, 'apply_coupon_code'])->name('cart.apply.coupon');
Route::delete('/cart/remove-coupon', [CartController::class, 'remove_coupon_code'])->name('cart.remove.coupon');

Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
Route::post('/place-an-order', [CartController::class, 'place_an_order'])->name('cart.place.an.order');
Route::get('/order-confirm', [CartController::class, 'order_confirmation'])->name('cart.order.confirm');

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/add', [WishlistController::class, 'add_to_wishlist'])->name('wishlist.add');
Route::delete('/wishlist/remove/{rowId}', [WishlistController::class, 'remove_item'])->name('wishlist.item.remove');
Route::delete('/wishlist/clear', [WishlistController::class, 'empty_wishlist'])->name('wishlist.clear');
Route::post('/wishlist/move-to-cart/{rowId}', [WishlistController::class, 'move_to_cart'])->name('wishlist.move.to.cart');


Route::middleware(['auth'])->group(function() {
    Route::get('/account-dashboard', [UserController::class, 'index'])->name('user.index');
    Route::get('/account-orders', [UserController::class, 'orders'])->name('user.orders');
    Route::get('/account-order/details/{order_id}', [UserController::class, 'order_details'])->name('user.order.details');
    Route::put('/account-order/cancel-order', [UserController::class, 'order_cancel'])->name('user.order.cancel');
});

Route::middleware(['auth', AuthAdmin::class])->group(function() {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

    Route::get('/brands', [AdminController::class, 'brands'])->name('admin.brands');
    Route::get('/brand/add', [AdminController::class, 'brand_add'])->name('admin.brand.add');
    Route::post('/brand/add/store', [AdminController::class, 'brand_store'])->name('admin.brand.store');
    Route::get('/brand/edit/{id}', [AdminController::class, 'brand_edit'])->name('admin.brand.edit');
    Route::put('/brand/update', [AdminController::class, 'brand_update'])->name('admin.brand.update');
    Route::delete('/brand/delete/{id}', [AdminController::class, 'brand_delete'])->name('admin.brand.delete');

    Route::get('/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::get('/category/add', [AdminController::class, 'category_add'])->name('admin.category.add');
    Route::post('/category/add/store', [AdminController::class, 'category_store'])->name('admin.category.store');
    Route::get('/category/edit/{id}', [AdminController::class, 'category_edit'])->name('admin.category.edit');
    Route::put('/category/update', [AdminController::class, 'category_update'])->name('admin.category.update');
    Route::delete('/category/delete/{id}', [AdminController::class, 'category_delete'])->name('admin.category.delete');

    Route::get('/products', [AdminController::class, 'products'])->name('admin.products');
    Route::get('/product/add', [AdminController::class, 'product_add'])->name('admin.product.add');
    Route::post('/product/add/store', [AdminController::class, 'product_store'])->name('admin.product.store');
    Route::get('/product/edit/{id}', [AdminController::class, 'product_edit'])->name('admin.product.edit');
    Route::put('/product/update', [AdminController::class, 'product_update'])->name('admin.product.update');
    Route::delete('/product/delete/{id}', [AdminController::class, 'product_delete'])->name('admin.product.delete');

    Route::get('/coupons', [AdminController::class, 'coupons'])->name('admin.coupons');
    Route::get('/coupon/add', [AdminController::class, 'coupon_add'])->name('admin.coupon.add');
    Route::post('/coupon/add/store', [AdminController::class, 'coupon_store'])->name('admin.coupon.store');
    Route::get('/coupon/edit/{id}', [AdminController::class, 'coupon_edit'])->name('admin.coupon.edit');
    Route::put('/coupon/update', [AdminController::class, 'coupon_update'])->name('admin.coupon.update');
    Route::delete('/coupon/delete/{id}', [AdminController::class, 'coupon_delete'])->name('admin.coupon.delete');

    Route::get('/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::get('/order/details/{order_id}', [AdminController::class, 'order_details'])->name('admin.order.details');
    Route::put('/order/update-status', [AdminController::class, 'update_order_status'])->name('admin.order.status.update');

    Route::get('/slides', [AdminController::class, 'slides'])->name('admin.slides');
    Route::get('/slide/add', [AdminController::class, 'slide_add'])->name('admin.slide.add');
    Route::post('/slide/add/store', [AdminController::class, 'slide_store'])->name('admin.slide.store');
    Route::get('/slide/edit/{id}', [AdminController::class, 'slide_edit'])->name('admin.slide.edit');
    Route::put('/slide/update', [AdminController::class, 'slide_update'])->name('admin.slide.update');
    Route::delete('/slide/delete/{id}', [AdminController::class, 'slide_delete'])->name('admin.slide.delete');

});