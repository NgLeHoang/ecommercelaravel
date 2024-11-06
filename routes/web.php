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

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/add', [WishlistController::class, 'add_to_wishlist'])->name('wishlist.add');
Route::delete('/wishlist/remove/{rowId}', [WishlistController::class, 'remove_item'])->name('wishlist.item.remove');
Route::delete('/wishlist/clear', [WishlistController::class, 'empty_wishlist'])->name('wishlist.clear');
Route::post('/wishlist/move-to-cart/{rowId}', [WishlistController::class, 'move_to_cart'])->name('wishlist.move.to.cart');


Route::middleware(['auth'])->group(function() {
    Route::get('/account-dashboard', [UserController::class, 'index'])->name('user.index');
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

});