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
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ColorController;

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
    Route::post('customers/statusChange', [CustomerController::class, 'statusChange'])->name('customers.statusChange');

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

    //Colors
    Route::post('colors/data', [ColorController::class, 'getData'])->name('colors.data');
    Route::post('colors/statusChange', [ColorController::class, 'statusChange'])->name('colors.statusChange');
    Route::post('colors/bulk-delete', [ColorController::class, 'bulkDelete'])->name('colors.bulkDelete');
    Route::resource('colors', ColorController::class);

    //Products — specific routes MUST come before resource to avoid {product} wildcard catching them
    Route::post('products/data', [ProductController::class, 'getData'])->name('products.data');
    Route::post('products/statusChange', [ProductController::class, 'statusChange'])->name('products.statusChange');
    Route::post('products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulkDelete');
    Route::post('products/delete-image', [ProductController::class, 'deleteImage'])->name('products.deleteImage');
    Route::post('products/set-primary-image', [ProductController::class, 'setPrimaryImage'])->name('products.setPrimaryImage');
    Route::get('products/categories', [ProductController::class, 'getCategories'])->name('products.categories');
    Route::get('products/sub-categories', [ProductController::class, 'getSubCategories'])->name('products.subCategories');
    Route::resource('products', ProductController::class);
});

require __DIR__.'/auth.php';
