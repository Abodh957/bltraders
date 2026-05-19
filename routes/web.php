<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.layouts.dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');
});

Route::middleware('auth')->prefix('admin')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('permissions', PermissionController::class);
    Route::post('permissions/data', [PermissionController::class, 'getData'])->name('permissions.data');

    Route::resource('roles', RoleController::class);
    Route::post('roles/data', [RoleController::class, 'getData'])->name('roles.data');

    //Customer
    Route::resource('customers', CustomerController::class);
    Route::post('customers/data', [CustomerController::class, 'getData'])->name('customers.data');

    //Stores
    Route::resource('stores', StoreController::class);
    Route::post('stores/data', [StoreController::class, 'getData'])->name('stores.data');
    Route::post('stores/statusChange', [StoreController::class, 'statusChange'])->name('stores.statusChange');
    Route::post('stores/bulk-delete', [StoreController::class, 'bulkDelete'])->name('stores.bulkDelete');

    //Category
    Route::resource('categories', CategoryController::class);
    Route::post('categories/data', [CategoryController::class, 'getData'])->name('categories.data');
    Route::post('categories/statusChange', [CategoryController::class, 'statusChange'])->name('categories.statusChange');
    Route::post('categories/bulk-delete', [CategoryController::class, 'bulkDelete'])->name('categories.bulkDelete');

    //Sub Category
    Route::resource('sub-categories', SubCategoryController::class);
    Route::post('sub-categories/data', [SubCategoryController::class, 'getData'])->name('subCategories.data');
    Route::post('sub-categories/statusChange', [SubCategoryController::class, 'statusChange'])->name('subCategories.statusChange');
    Route::post('sub-categories/bulk-delete', [SubCategoryController::class, 'bulkDelete'])->name('sub-categories.bulkDelete');

    //User Shops
    Route::resource('user-shops', ShopController::class);
    Route::post('user-shops/data', [ShopController::class, 'getData'])->name('user-shops.data');
    Route::post('user-shops/statusChange', [ShopController::class, 'statusChange'])->name('user-shops.statusChange');

    //Banners
    Route::resource('banners', BannerController::class);
    Route::post('banners/data', [BannerController::class, 'getData'])->name('banners.data');
    Route::post('banners/statusChange', [BannerController::class, 'statusChange'])->name('banners.statusChange');
    Route::post('banners/bulk-delete', [BannerController::class, 'bulkDelete'])->name('banners.bulkDelete');

    //Brands
    Route::resource('brands', BrandController::class);
    Route::post('brands/data', [BrandController::class, 'getData'])->name('brands.data');
    Route::post('brands/statusChange', [BrandController::class, 'statusChange'])->name('brands.statusChange');
    Route::post('brands/bulk-delete', [BrandController::class, 'bulkDelete'])->name('brands.bulkDelete');
});

require __DIR__.'/auth.php';
