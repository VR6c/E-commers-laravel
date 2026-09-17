<?php

use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutApiController;
use App\Http\Controllers\Api\CouponApiController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\CustomerProfileController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductReviewController;
use App\Http\Controllers\Api\RecipeApiController;
use App\Http\Controllers\Api\SocialMediaLinkController;
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
    Route::post('login', [CustomerAuthController::class, 'login']);
    Route::post('refresh', [CustomerAuthController::class, 'refresh']);
    Route::post('refresh-token', [CustomerAuthController::class, 'refresh']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [CustomerProfileController::class, 'getProfile']);
        Route::put('profile', [CustomerProfileController::class, 'updateProfile']);
        Route::post('avatar', [CustomerProfileController::class, 'updateAvatar']);
        Route::delete('avatar', [CustomerProfileController::class, 'deleteAvatar']);
        Route::post('logout', [CustomerAuthController::class, 'logout']);
    });
});

// ──────────────────────────────────────────────────────────────
// Password Reset (public — no auth required)
// ──────────────────────────────────────────────────────────────
Route::prefix('password')->group(function () {
    Route::post('forgot', [PasswordResetController::class, 'forgot']);
    Route::post('reset', [PasswordResetController::class, 'reset']);
});

// ──────────────────────────────────────────────────────────────
// Catalog (public)
// ──────────────────────────────────────────────────────────────
Route::get('/banners', [BannerController::class, 'index']);
Route::apiResource('brands', BrandController::class);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/social-media-links', [SocialMediaLinkController::class, 'index']);

// Products — suggestions, list (with search/filter) and detail
Route::get('/products/suggestions', [ProductController::class, 'suggestions']);
Route::get('/products/{slug}/suggestions', [ProductController::class, 'related']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

// Recipes — featured & list (public), detail (public summary / gated full)
Route::get('/recipes/featured', [RecipeApiController::class, 'featured']);
Route::get('/recipes', [RecipeApiController::class, 'index']);
Route::get('/recipes/{slug}', [RecipeApiController::class, 'show']);

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
    Route::post('/checkout', [CheckoutApiController::class, 'process']);
    Route::get('/checkout/payment-status', [CheckoutApiController::class, 'checkPaymentStatus']);

    // Orders
    Route::get('/orders', [OrderApiController::class, 'index']);
    Route::get('/orders/{id}', [OrderApiController::class, 'show']);
    Route::get('/orders/{id}/receipt', [OrderApiController::class, 'receipt']);

    // Wishlist
    Route::get('/wishlist', [WishlistApiController::class, 'index']);
    Route::get('/wishlist/ids', [WishlistApiController::class, 'ids']);
    Route::post('/wishlist/toggle', [WishlistApiController::class, 'toggle']);
    Route::delete('/wishlist/{product_id}', [WishlistApiController::class, 'destroy']);

    // Wishlist aliases (support plural form for backward compatibility)
    Route::get('/wishlists', [WishlistApiController::class, 'index']);
    Route::post('/wishlists/toggle', [WishlistApiController::class, 'toggle']);
    Route::post('/wishlists/{product_id}/toggle', function (Request $request, $productId) {
        $request->merge(['product_id' => $productId]);

        return app(WishlistApiController::class)->toggle($request);
    });

    // Recipes — unlocked recipes for the authenticated customer
    Route::get('/recipes/unlocked', [RecipeApiController::class, 'unlocked']);
});

Route::get('/seed-products', function () {
    try {
        $count = (int) request()->get('count', 100);
        $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
        try {
            $pdo->exec('ROLLBACK;');
        } catch (\Throwable $t) {
        }

        $seeder = new \Database\Seeders\BulkProductSeeder;
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

Route::match(['get', 'post'], '/fix-duplicate-images', function (Request $request) {
    try {
        $dryRun = (bool) $request->input('dry_run', false);
        $allMode = (bool) $request->input('all', false);

        $mapFile = database_path('seeders/unique_photos_map.json');
        if (! file_exists($mapFile)) {
            return response()->json([
                'status' => 'error',
                'message' => "Map file not found at {$mapFile}",
            ], 404);
        }

        $photoMap = json_decode(file_get_contents($mapFile), true);
        $images = \App\Models\ProductImage::where('type', 'thumb')->with('product')->get();

        if ($images->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No product thumbnails found in database.',
                'updated' => 0,
            ]);
        }

        $urlCounts = $images->groupBy('image_url')->map->count();
        $duplicateUrls = $urlCounts->filter(fn ($c) => $c > 1);

        $toUpdate = [];
        $usedAssignedUrls = [];

        foreach ($images as $img) {
            $product = $img->product;
            if (! $product) {
                continue;
            }

            $currentUrl = $img->image_url;
            $isDuplicate = isset($duplicateUrls[$currentUrl]);

            if ($allMode || $isDuplicate) {
                $productName = $product->name;
                $photoId = $photoMap[$productName] ?? null;

                if (! $photoId) {
                    foreach ($photoMap as $mapName => $id) {
                        if (strcasecmp($mapName, $productName) === 0 || \Illuminate\Support\Str::slug($mapName) === \Illuminate\Support\Str::slug($productName)) {
                            $photoId = $id;
                            break;
                        }
                    }
                }

                if ($photoId) {
                    $newUrl = "https://images.unsplash.com/photo-{$photoId}?w=600&h=600&fit=crop&auto=format&q=75";

                    if (! $allMode && $currentUrl === $newUrl && ! isset($usedAssignedUrls[$newUrl])) {
                        $usedAssignedUrls[$newUrl] = true;

                        continue;
                    }

                    $usedAssignedUrls[$newUrl] = true;

                    $toUpdate[] = [
                        'image_id' => $img->id,
                        'product_id' => $product->id,
                        'product_name' => $productName,
                        'old_url' => $currentUrl,
                        'new_url' => $newUrl,
                        'new_name' => \Illuminate\Support\Str::slug($productName).'.jpg',
                    ];
                }
            }
        }

        if ($dryRun) {
            return response()->json([
                'status' => 'preview',
                'dry_run' => true,
                'total_scanned' => $images->count(),
                'duplicate_urls' => $duplicateUrls->count(),
                'to_update' => count($toUpdate),
                'sample_records' => array_slice($toUpdate, 0, 20),
            ]);
        }

        if (empty($toUpdate)) {
            return response()->json([
                'status' => 'success',
                'message' => 'All product thumbnails are already unique! No updates needed.',
                'updated' => 0,
            ]);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        foreach ($toUpdate as $item) {
            \App\Models\ProductImage::where('id', $item['image_id'])->update([
                'image_url' => $item['new_url'],
                'name' => $item['new_name'],
            ]);
        }
        \Illuminate\Support\Facades\DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully updated '.count($toUpdate).' product images with unique URLs!',
            'updated_count' => count($toUpdate),
            'sample_records' => array_slice($toUpdate, 0, 10),
        ]);
    } catch (\Throwable $e) {
        if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
            \Illuminate\Support\Facades\DB::rollBack();
        }

        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }
});
