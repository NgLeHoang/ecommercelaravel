<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home.index');

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

});