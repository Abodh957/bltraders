<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\MainCategoryController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\ProductController;
use App\Models\MainCategory;
Route::get('/', function () {
    return view('welcome');
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

    //Main Categories
    Route::resource('main-categories', MainCategoryController::class);
    Route::post('main-categories/data', [MainCategoryController::class, 'getData'])->name('main-categories.data');
    Route::post('main-categories/statusChange', [MainCategoryController::class, 'statusChange'])->name('main-categories.statusChange');

    //Category
    Route::resource('categories', CategoryController::class);
    Route::post('categories/data', [CategoryController::class, 'getData'])->name('categories.data');
    Route::post('categories/statusChange', [CategoryController::class, 'statusChange'])->name('categories.statusChange');

    //Sub Category
    Route::resource('sub-categories', SubCategoryController::class);
    Route::post('sub-categories/data', [SubCategoryController::class, 'getData'])->name('subCategories.data');
    Route::post('sub-categories/statusChange', [SubCategoryController::class, 'statusChange'])->name('subCategories.statusChange');
});

require __DIR__.'/auth.php';
