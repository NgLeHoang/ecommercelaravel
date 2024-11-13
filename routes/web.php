<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Guest\CartController;
use App\Http\Controllers\Guest\HomeController;
use App\Http\Controllers\Guest\ShopController;
use App\Http\Controllers\Guest\WishlistController;
use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Auth::routes();

// Guest Routes
Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/contact', [HomeController::class, 'contact'])->name('home.contact');
Route::post('/contact/store', [HomeController::class, 'storeContact'])->name('home.contact.store');
Route::get('/about', [HomeController::class, 'about'])->name('home.about');

// Shop Routes
Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/', [ShopController::class, 'index'])->name('index');
    Route::get('/{product_slug}', [ShopController::class, 'productDetails'])->name('product.details');
});

// Cart Routes
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'addToCart'])->name('add');
    Route::put('/adjust-quantity/{rowId}/{action}', [CartController::class, 'adjustCartQuantity'])->name('quantity.adjust');
    Route::delete('/remove/{rowId}', [CartController::class, 'removeItem'])->name('item.remove');
    Route::delete('/clear', [CartController::class, 'emptyCart'])->name('clear');
    Route::post('/apply-coupon', [CartController::class, 'applyCouponCode'])->name('apply.coupon');
    Route::delete('/remove-coupon', [CartController::class, 'removeCouponCode'])->name('remove.coupon');
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/place-an-order', [CartController::class, 'placeAnOrder'])->name('place.an.order');
    Route::get('/order-confirm', [CartController::class, 'orderConfirmation'])->name('order.confirm');
});

// Wishlist Routes
Route::prefix('wishlist')->name('wishlist.')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('index');
    Route::post('/add', [WishlistController::class, 'addToWishlist'])->name('add');
    Route::delete('/remove/{rowId}', [WishlistController::class, 'removeItem'])->name('item.remove');
    Route::delete('/clear', [WishlistController::class, 'emptyWishlist'])->name('clear');
    Route::post('/move-to-cart/{rowId}', [WishlistController::class, 'moveToCart'])->name('move.to.cart');
});

Route::middleware(['auth'])->prefix('account')->name('user.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [UserController::class, 'index'])->name('index');

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/', [UserController::class, 'getUserOrders'])->name('orders');
        Route::get('/details/{order_id}', [UserController::class, 'showOrderDetails'])->name('order.details');
        Route::put('/cancel', [UserController::class, 'cancelUserOrder'])->name('order.cancel');
    });

    // Address Management
    Route::prefix('address')->name('address.')->group(function () {
        Route::get('/', [UserController::class, 'showUserAddress'])->name('index');
        Route::get('/add', [UserController::class, 'addAddress'])->name('add');
        Route::post('/store', [UserController::class, 'storeUserAddress'])->name('store');
        Route::get('/edit/{id}', [UserController::class, 'editUserAddress'])->name('edit');
        Route::put('/update', [UserController::class, 'updateAddress'])->name('update');
    });

    // Account Details
    Route::get('/details', [UserController::class, 'accountDetails'])->name('account.details');
    Route::put('/details/store', [UserController::class, 'updateUserAccountDetails'])->name('account.store');
});


Route::middleware(['auth', AuthAdmin::class])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [AdminController::class, 'index'])->name('index');

    // Brand Management
    Route::prefix('brands')->name('brands.')->group(function () {
        Route::get('/', [BrandController::class, 'brands'])->name('index');
        Route::get('/add', [BrandController::class, 'addBrand'])->name('add');
        Route::post('/store', [BrandController::class, 'storeBrand'])->name('store');
        Route::get('/edit/{id}', [BrandController::class, 'editBrand'])->name('edit');
        Route::put('/update', [BrandController::class, 'updateBrand'])->name('update');
        Route::delete('/delete/{id}', [BrandController::class, 'deleteBrand'])->name('delete');
    });

    // Category Management
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [AdminController::class, 'categories'])->name('index');
        Route::get('/add', [AdminController::class, 'addCategory'])->name('add');
        Route::post('/store', [AdminController::class, 'storeCategory'])->name('store');
        Route::get('/edit/{id}', [AdminController::class, 'editCategory'])->name('edit');
        Route::put('/update', [AdminController::class, 'updateCategory'])->name('update');
        Route::delete('/delete/{id}', [AdminController::class, 'deleteCategory'])->name('delete');
    });

    // Product Management
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [AdminController::class, 'products'])->name('index');
        Route::get('/add', [AdminController::class, 'addProduct'])->name('add');
        Route::post('/store', [AdminController::class, 'storeProduct'])->name('store');
        Route::get('/edit/{id}', [AdminController::class, 'editProduct'])->name('edit');
        Route::put('/update', [AdminController::class, 'updateProduct'])->name('update');
        Route::delete('/delete/{id}', [AdminController::class, 'deleteProduct'])->name('delete');
    });

    // Coupon Management
    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::get('/', [AdminController::class, 'coupons'])->name('index');
        Route::get('/add', [AdminController::class, 'addCoupon'])->name('add');
        Route::post('/store', [AdminController::class, 'storeCoupon'])->name('store');
        Route::get('/edit/{id}', [AdminController::class, 'editCoupon'])->name('edit');
        Route::put('/update', [AdminController::class, 'updateCoupon'])->name('update');
        Route::delete('/delete/{id}', [AdminController::class, 'deleteCoupon'])->name('delete');
    });

    // Order Management
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [AdminController::class, 'orders'])->name('index');
        Route::get('/details/{order_id}', [AdminController::class, 'orderDetails'])->name('details');
        Route::put('/update-status', [AdminController::class, 'updateOrderStatus'])->name('status.update');
    });

    // Slide Management
    Route::prefix('slides')->name('slides.')->group(function () {
        Route::get('/', [AdminController::class, 'slides'])->name('index');
        Route::get('/add', [AdminController::class, 'addSlide'])->name('add');
        Route::post('/store', [AdminController::class, 'storeSlide'])->name('store');
        Route::get('/edit/{id}', [AdminController::class, 'editSlide'])->name('edit');
        Route::put('/update', [AdminController::class, 'updateSlide'])->name('update');
        Route::delete('/delete/{id}', [AdminController::class, 'deleteSlide'])->name('delete');
    });

    // Contact Management
    Route::prefix('contacts')->name('contacts.')->group(function () {
        Route::get('/', [AdminController::class, 'contacts'])->name('index');
        Route::delete('/delete/{id}', [AdminController::class, 'deleteContact'])->name('delete');
    });
});
