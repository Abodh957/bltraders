<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SubCategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/add-shop', [AuthController::class, 'addShop']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Banner API (public)
Route::get('/banners', [BannerController::class, 'index']);
Route::get('/banners/{id}', [BannerController::class, 'show']);

// Banner API (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/banners', [BannerController::class, 'store']);
    Route::post('/banners/{id}', [BannerController::class, 'update']);
    Route::delete('/banners/{id}', [BannerController::class, 'destroy']);
    Route::post('/banners-bulk-delete', [BannerController::class, 'bulkDelete']);
});

// Brand API (public)
Route::get('/brands', [BrandController::class, 'index']);
Route::get('/brands/{id}', [BrandController::class, 'show']);

// Brand API (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/brands', [BrandController::class, 'store']);
    Route::post('/brands/{id}', [BrandController::class, 'update']);
    Route::delete('/brands/{id}', [BrandController::class, 'destroy']);
    Route::post('/brands-bulk-delete', [BrandController::class, 'bulkDelete']);
});

// Store API (public)
Route::get('/stores', [StoreController::class, 'index']);
Route::get('/stores/{id}', [StoreController::class, 'show']);

// Store selection (protected) — sets the store for the logged-in customer.
// After this, products/categories/sub-categories/brands/banners all return that
// store's data automatically, without needing ?store_id= on every call.
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/store/select', [StoreController::class, 'select']);
    Route::get('/store/selected', [StoreController::class, 'selected']);
    Route::delete('/store/selected', [StoreController::class, 'clearSelected']);
});

// Store API (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/stores', [StoreController::class, 'store']);
    Route::post('/stores/{id}', [StoreController::class, 'update']);
    Route::delete('/stores/{id}', [StoreController::class, 'destroy']);
});

// Category API (public) — filter by ?store_id=1
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// Category API (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::post('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
});

// Sub Category API (public) — filter by ?store_id=1 or ?category_id=1
Route::get('/sub-categories', [SubCategoryController::class, 'index']);
Route::get('/sub-categories/{id}', [SubCategoryController::class, 'show']);

// Sub Category API (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/sub-categories', [SubCategoryController::class, 'store']);
    Route::post('/sub-categories/{id}', [SubCategoryController::class, 'update']);
    Route::delete('/sub-categories/{id}', [SubCategoryController::class, 'destroy']);
});

// Product API (public)
// GET /api/products                          — list (paginated)
// GET /api/products?store_id=1               — filter by store
// GET /api/products?category_id=1            — filter by category
// GET /api/products?sub_category_id=1        — filter by sub-category
// GET /api/products?search=yoga              — search by name/sku
// GET /api/products?featured=1              — featured only
// GET /api/products?per_page=10&page=2       — pagination
// GET /api/products/trending                 — best sellers (most ordered)
// GET /api/products/trending?days=30         — trending in the last 30 days
// GET /api/products/trending?store_id=1      — best sellers of one store
// GET /api/products/{id}                     — single product detail
Route::get('/products', [ProductController::class, 'index']);
// NOTE: /products/trending MUST stay above /products/{id}, otherwise "trending"
// is captured as an {id} and the request 404s.
Route::get('/products/trending', [ProductController::class, 'trending']);
Route::get('/products/{id}', [ProductController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Customer APIs — cart & orders (login + approved shop required)
|--------------------------------------------------------------------------
| The cart is store-wise: a user holds one cart per store and each store
| checks out into its own order, with its own GST/billing breakup.
|
| Cart
|   GET    /api/cart                       — all store carts
|   GET    /api/cart?store_id=1            — one store's cart
|   GET    /api/cart/count                 — total items (header badge)
|   GET    /api/cart/summary?store_id=1    — billing preview before checkout
|   POST   /api/cart/add                   — { product_id, quantity?, color_id?, replace? }
|   POST   /api/cart/update/{itemId}       — { quantity }  (0 removes the line)
|   DELETE /api/cart/item/{itemId}         — remove a line
|   DELETE /api/cart?store_id=1            — empty one cart (omit store_id for all)
|
| Orders
|   POST   /api/orders                     — place order from a store cart
|   GET    /api/orders                     — my orders (?status= &store_id= &per_page=)
|   GET    /api/orders/{id}                — order detail + timeline
|   POST   /api/orders/{id}/cancel         — { reason? }
|   GET    /api/orders/{id}/invoice        — invoice / billing document
*/
Route::middleware(['auth:sanctum', 'shop.approved'])->group(function () {
    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::get('/cart/count', [CartController::class, 'count']);
    Route::get('/cart/summary', [CartController::class, 'summary']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::post('/cart/update/{itemId}', [CartController::class, 'update']);
    Route::delete('/cart/item/{itemId}', [CartController::class, 'removeItem']);
    Route::delete('/cart', [CartController::class, 'clear']);

    // Orders
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::get('/orders/{id}/invoice', [OrderController::class, 'invoice']);
});
