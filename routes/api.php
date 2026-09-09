<?php

use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponApiController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\CustomerProfileController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductReviewController;
use App\Http\Controllers\Api\SocialMediaLinkController;
use App\Http\Controllers\Api\CheckoutApiController;
use App\Http\Controllers\Api\WishlistApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ──────────────────────────────────────────────────────────────
// Authentication
// ──────────────────────────────────────────────────────────────
Route::prefix('customer')->group(function () {
    Route::post('register', [CustomerAuthController::class, 'register']);
    Route::post('login',    [CustomerAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile',  [CustomerAuthController::class, 'profile']);
        Route::put('profile',  [CustomerProfileController::class, 'update']);
        Route::post('logout',  [CustomerAuthController::class, 'logout']);
    });
});

// ──────────────────────────────────────────────────────────────
// Password Reset (public — no auth required)
// ──────────────────────────────────────────────────────────────
Route::prefix('password')->group(function () {
    Route::post('forgot', [PasswordResetController::class, 'forgot']);
    Route::post('reset',  [PasswordResetController::class, 'reset']);
});

// ──────────────────────────────────────────────────────────────
// Catalog (public)
// ──────────────────────────────────────────────────────────────
Route::get('/banners',          [BannerController::class, 'index']);
Route::apiResource('brands',    BrandController::class);
Route::get('/categories',       [CategoryController::class, 'index']);
Route::get('/social-media-links', [SocialMediaLinkController::class, 'index']);

// Products — list (with search/filter) and detail
Route::get('/products',          [ProductController::class, 'index']);
Route::get('/products/{slug}',   [ProductController::class, 'show']);

// Product reviews — read is public, write requires auth
Route::get('/products/{slug}/reviews', [ProductReviewController::class, 'index']);
Route::post('/products/{slug}/reviews', [ProductReviewController::class, 'store'])
    ->middleware('auth:sanctum');

// ──────────────────────────────────────────────────────────────
// Shopping (auth required)
// ──────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    // Coupon
    Route::post('/coupons/validate', [CouponApiController::class, 'apply']);

    // Checkout & payment status
    Route::post('/checkout',                [CheckoutApiController::class, 'process']);
    Route::get('/checkout/payment-status',  [CheckoutApiController::class, 'checkPaymentStatus']);

    // Orders
    Route::get('/orders',      [OrderApiController::class, 'index']);
    Route::get('/orders/{id}', [OrderApiController::class, 'show']);

    // Wishlist
    Route::get('/wishlist',                     [WishlistApiController::class, 'index']);
    Route::get('/wishlist/ids',                 [WishlistApiController::class, 'ids']);
    Route::post('/wishlist/toggle',             [WishlistApiController::class, 'toggle']);
    Route::delete('/wishlist/{product_id}',     [WishlistApiController::class, 'destroy']);
});

Route::get('/seed-products', function () {
    try {
        $count = (int) request()->get('count', 100);
        $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
        try {
            $pdo->exec('ROLLBACK;');
        } catch (\Throwable $t) {
        }

        $seeder = new \Database\Seeders\BulkProductSeeder();
        $seeded = $seeder->seedProducts($count);

        return response()->json([
            'status' => 'success',
            'message' => "Successfully seeded {$seeded} products to production database!",
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }
});
