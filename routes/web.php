<?php

use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentGatewayConfigController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductReviewController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\SocialMediaLinkController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\SiteSettingsController;
use App\Http\Controllers\Store\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::redirect('/home', '/');

Route::get('/login', function () {
    return view('admin.auth.login');
});

Route::get('/migrate', function () {
    try {
        if (request()->has('fresh')) {
            \Illuminate\Support\Facades\Schema::dropAllTables();
        }
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return response()->json([
            'status' => 'success',
            'message' => 'Database migrated successfully!',
            'output' => \Illuminate\Support\Facades\Artisan::output(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 200);
    }
});



Route::get('/test-db', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        return response()->json([
            'status' => 'success',
            'message' => 'Connected successfully to ' . \Illuminate\Support\Facades\DB::connection()->getDatabaseName(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 200);
    }
});



Auth::routes();

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {

    /* Dashboard */
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [AdminDashboardController::class, 'chartData'])->name('dashboard.chart-data');

    /* Categories */
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::post('categories/data', [CategoryController::class, 'getCategories'])->name('categories.data');
    Route::post('categories/update-status', [CategoryController::class, 'updateStatus'])->name('categories.updateStatus');

    /* Products */
    Route::resource('products', ProductController::class);
    Route::post('products/data', [ProductController::class, 'getProducts'])->name('products.data');
    Route::post('products/updateStatus', [ProductController::class, 'updateStatus'])->name('products.updateStatus');

    /* Brands */
    Route::resource('brands', BrandController::class);
    Route::get('brands-data', [BrandController::class, 'getData'])->name('brands.getData');
    Route::post('brands/update-status', [BrandController::class, 'updateStatus'])->name('brands.updateStatus');

    /* Menus */
    Route::resource('menus', MenuController::class);
    Route::post('menus/data', [MenuController::class, 'getData'])->name('menus.data');
    Route::resource('menus.items', MenuItemController::class)->shallow();
    Route::get('menus-items', [MenuItemController::class, 'index'])->name('menus.item.index');
    Route::post('menus-items/getdata', [MenuItemController::class, 'getData'])->name('menus.item.getData');

    /* Banners */
    Route::resource('banners', BannerController::class)->except(['show']);
    Route::post('banners/data', [BannerController::class, 'getData'])->name('banners.data');
    Route::post('/banners/update-status', [BannerController::class, 'updateStatus'])->name('banners.updateStatus');

    /* Social Media Links */
    Route::resource('social-media-links', SocialMediaLinkController::class);
    Route::post('social-media-links/data', [SocialMediaLinkController::class, 'getData'])->name('social-media-links.data');

    /* Orders */
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/pending', [OrderController::class, 'pending'])->name('orders.pending');
    Route::get('orders/completed', [OrderController::class, 'completed'])->name('orders.completed');
    Route::delete('orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::post('orders/data', [OrderController::class, 'getData'])->name('orders.data');

    /* Product Variants */
    Route::resource('product_variants', ProductVariantController::class);
    Route::post('/product_variants/data', [ProductVariantController::class, 'getData'])->name('product_variants.data');

    /* Customers */
    Route::resource('customers', CustomerController::class);
    Route::post('customers/data', [CustomerController::class, 'getCustomerData'])->name('customers.data');

    /* Reviews */
    Route::post('/reviews/data', [ProductReviewController::class, 'getData'])->name('reviews.data');
    Route::patch('/reviews/{review}/toggle-approve', [ProductReviewController::class, 'toggleApprove'])->name('reviews.toggleApprove');
    Route::resource('reviews', ProductReviewController::class)->except(['create', 'store']);

    /* Attributes */
    Route::resource('attributes', AttributeController::class);

    /* Attribute Value Management */
    Route::post('attributes/{attribute}/values', [AttributeController::class, 'storeValue'])->name('attributes.values.store');
    Route::delete('values/{value}', [AttributeController::class, 'destroyValue'])->name('values.destroy');
    Route::post('attributes/data', [AttributeController::class, 'getAttributesData'])->name('attributes.data');

    /* Vendors */
    Route::get('vendors', [VendorController::class, 'index'])->name('vendors.index');
    Route::post('vendors/data', [VendorController::class, 'getVendorData'])->name('vendors.data');
    Route::get('vendors/create', [VendorController::class, 'create'])->name('vendors.create');
    Route::post('vendors', [VendorController::class, 'store'])->name('vendors.store');
    Route::get('vendors/{id}/edit', [VendorController::class, 'edit'])->name('vendors.edit');
    Route::put('vendors/{id}', [VendorController::class, 'update'])->name('vendors.update');
    Route::delete('vendors/{id}', [VendorController::class, 'destroy'])->name('vendors.destroy');

    /* Pages */
    Route::resource('pages', PageController::class);
    Route::post('pages/update-status', [PageController::class, 'updateStatus'])->name('pages.updateStatus');
    Route::post('pages/data', [PageController::class, 'data'])->name('pages.data');

    /* payments */
    Route::get('payments/get-data', [PaymentController::class, 'getData'])->name('payments.getData');
    Route::resource('payments', PaymentController::class)->only(['index', 'destroy', 'show']);

    /* Refunds */
    Route::get('refunds', [RefundController::class, 'index'])->name('refunds.index');
    Route::get('refunds/data', [RefundController::class, 'getData'])->name('refunds.getData');
    Route::delete('refunds/{refund}', [RefundController::class, 'destroy'])->name('refunds.destroy');
    Route::get('refunds/{refund}', [RefundController::class, 'show'])->name('refunds.show');

    /* Payment Gateways */
    Route::get('payment-gateways', [PaymentGatewayController::class, 'index'])->name('payment-gateways.index');
    Route::get('payment-gateways/data', [PaymentGatewayController::class, 'getData'])->name('payment-gateways.getData');
    Route::get('payment-gateways/{paymentGateway}/edit', [PaymentGatewayController::class, 'edit'])->name('payment-gateways.edit');
    Route::put('payment-gateways/{paymentGateway}', [PaymentGatewayController::class, 'update'])->name('payment-gateways.update');
    Route::delete('payment-gateways/{paymentGateway}', [PaymentGatewayController::class, 'destroy'])->name('payment-gateways.destroy');

    /* Coupons */
    Route::resource('coupons', CouponController::class);
    Route::post('coupons/data', [CouponController::class, 'getData'])->name('coupons.data');

    /* Currencies */
    Route::resource('currencies', CurrencyController::class);
    Route::get('currencies/data', [CurrencyController::class, 'getData'])->name('currencies.data');

    /* Shops */
    Route::resource('shops', ShopController::class);
    Route::get('shops/data', [ShopController::class, 'getData'])->name('shops.data');

    /* Payment Gateways Configs */
    Route::get('payment_gateway_configs/getData', [PaymentGatewayConfigController::class, 'getData'])->name('payment_gateway_configs.getData');
    Route::resource('payment_gateway_configs', PaymentGatewayConfigController::class)->except(['show']);

    /* Profile */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /* Site Settings */
    Route::get('site-settings', [SiteSettingsController::class, 'index'])->name('site-settings.index');
    Route::get('site-settings/edit', [SiteSettingsController::class, 'edit'])->name('site-settings.edit');
    Route::put('site-settings/update', [SiteSettingsController::class, 'update'])->name('site-settings.update');
});

Route::get('/checkout/paypal/success', [CheckoutController::class, 'paypalSuccess'])
    ->name('paypal.success');
// PayPal cancel callback
Route::get('/checkout/paypal/cancel', [CheckoutController::class, 'paypalCancel'])
    ->name('paypal.cancel');

// ABA PayWay success callback
Route::get('/checkout/payway/success', [CheckoutController::class, 'paywaySuccess'])
    ->name('payway.success');
// ABA PayWay cancel callback
Route::get('/checkout/payway/cancel', [CheckoutController::class, 'paywayCancel'])
    ->name('payway.cancel');
// ABA PayWay server-to-server callback (pushback webhook)
Route::post('/checkout/payway/callback', [CheckoutController::class, 'paywayCallback'])
    ->name('payway.callback');
// Storefront thank you success page
Route::get('/thank-you', [CheckoutController::class, 'thankYou'])
    ->name('thankyou');

// ABA PayWay Hosted HTML Page
Route::get('/checkout/payway/hosted', [CheckoutController::class, 'paywayHosted'])
    ->name('payway.hosted');
// ABA PayWay Hosted HTML Page for mobile app
Route::get('/checkout/payway/hosted-mobile/{order_id}', [CheckoutController::class, 'paywayHostedMobile'])
    ->name('payway.hosted-mobile');
// ABA PayWay Check Transaction status endpoint (AJAX polling)
Route::get('/checkout/payway/status/{tran_id}', [CheckoutController::class, 'checkPaywayStatus'])
    ->name('payway.status');
