<?php

use App\Http\Controllers\Api\V1\Admin\AuditLogController;
use App\Http\Controllers\Api\V1\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\V1\Admin\BlogController;
use App\Http\Controllers\Api\V1\Admin\CouponController;
use App\Http\Controllers\Api\V1\Admin\CustomerController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\HomepageBuilderController;
use App\Http\Controllers\Api\V1\Admin\InventoryController;
use App\Http\Controllers\Api\V1\Admin\LogisticsController;
use App\Http\Controllers\Api\V1\Admin\MediaController;
use App\Http\Controllers\Api\V1\Admin\MessageController;
use App\Http\Controllers\Api\V1\Admin\OrderController;
use App\Http\Controllers\Api\V1\Admin\ProductController;
use App\Http\Controllers\Api\V1\Admin\ReportController;
use App\Http\Controllers\Api\V1\Admin\ResourceController;
use App\Http\Controllers\Api\V1\Admin\ReturnController;
use App\Http\Controllers\Api\V1\Admin\ReviewController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Admin\SettingsController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Customer\CustomerAuthController;
use App\Http\Controllers\Api\V1\Customer\CustomerOrderController;
use App\Http\Controllers\Api\V1\Public\CartController;
use App\Http\Controllers\Api\V1\Public\CheckoutController;
use App\Http\Controllers\Api\V1\Public\PublicController;
use App\Http\Controllers\Api\V1\Public\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes V1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // -------------------------------------------------------------
    // Public API: /api/v1/public
    // -------------------------------------------------------------
    Route::prefix('public')->group(function () {
        Route::get('/homepage', [PublicController::class, 'homepage']);
        Route::get('/categories', [PublicController::class, 'categories']);
        Route::get('/products', [PublicController::class, 'products']);
        Route::get('/products/{slug}', [PublicController::class, 'productDetail']);
        Route::post('/products/{id}/reviews', [PublicController::class, 'addReview']);
        Route::get('/search', [PublicController::class, 'search']);
        Route::get('/collections', [PublicController::class, 'collections']);
        Route::get('/collections/{slug}', [PublicController::class, 'collectionDetail']);
        Route::get('/pages/{slug}', [PublicController::class, 'page']);
        Route::post('/contact', [PublicController::class, 'submitContact']);
        Route::post('/newsletter/subscribe', [PublicController::class, 'subscribeNewsletter']);
        Route::get('/settings', [PublicController::class, 'settings']);
        Route::get('/shipping-methods', [CheckoutController::class, 'getShippingMethods']);
        Route::post('/track-order', [CheckoutController::class, 'trackOrder']);

        // Shopping Cart
        Route::get('/cart', [CartController::class, 'get']);
        Route::post('/cart/add', [CartController::class, 'add']);
        Route::put('/cart/items/{id}', [CartController::class, 'updateQuantity']);
        Route::delete('/cart/items/{id}', [CartController::class, 'remove']);
        Route::post('/cart/coupon', [CartController::class, 'validateCoupon']);

        // Wishlist
        Route::get('/wishlist', [WishlistController::class, 'index']);
        Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);

        // Checkout
        Route::post('/checkout', [CheckoutController::class, 'process']);
    });

    // -------------------------------------------------------------
    // Customer API: /api/v1/customer
    // -------------------------------------------------------------
    Route::prefix('customer')->group(function () {
        Route::post('/register', [CustomerAuthController::class, 'register']);
        Route::post('/login', [CustomerAuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [CustomerAuthController::class, 'me']);
            Route::put('/profile', [CustomerAuthController::class, 'updateProfile']);
            Route::post('/logout', [CustomerAuthController::class, 'logout']);

            Route::get('/orders', [CustomerOrderController::class, 'orders']);
            Route::get('/orders/{orderNumber}', [CustomerOrderController::class, 'orderDetail']);
            Route::get('/addresses', [CustomerOrderController::class, 'getAddresses']);
            Route::post('/addresses', [CustomerOrderController::class, 'storeAddress']);
            Route::delete('/addresses/{id}', [CustomerOrderController::class, 'deleteAddress']);
        });
    });

    // -------------------------------------------------------------
    // Admin API: /api/v1/admin
    // -------------------------------------------------------------
    Route::prefix('admin')->group(function () {
        Route::post('/login', [AdminAuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AdminAuthController::class, 'me']);
            Route::post('/logout', [AdminAuthController::class, 'logout']);

            // Dashboard & Badges
            Route::get('/dashboard', [DashboardController::class, 'index']);
            Route::get('/dashboard/badges', [DashboardController::class, 'badges']);

            // Reports & Analytics
            Route::get('/reports', [ReportController::class, 'index']);
            Route::get('/reports/sales', [ReportController::class, 'sales']);

            // Customers Directory
            Route::get('/customers', [CustomerController::class, 'index']);
            Route::get('/customers/{id}', [CustomerController::class, 'show']);

            // Coupons & Promotions
            Route::apiResource('coupons', CouponController::class);

            // Inquiries & B2B Messages
            Route::get('/messages', [MessageController::class, 'index']);
            Route::put('/messages/{id}', [MessageController::class, 'update']);
            Route::delete('/messages/{id}', [MessageController::class, 'destroy']);

            // Returns & Exchanges
            Route::get('/returns', [ReturnController::class, 'index']);
            Route::put('/returns/{id}', [ReturnController::class, 'update']);

            // Logistics & Courier (Steadfast)
            Route::get('/logistics', [LogisticsController::class, 'index']);
            Route::post('/logistics/consignment', [LogisticsController::class, 'createConsignment']);
            Route::get('/logistics/{id}/track', [LogisticsController::class, 'trackStatus']);

            // Reviews & Ratings Moderation
            Route::get('/reviews', [ReviewController::class, 'index']);
            Route::put('/reviews/{id}/toggle-approval', [ReviewController::class, 'toggleApproval']);
            Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

            // Blog & Editorial Engine
            Route::apiResource('blog', BlogController::class);

            // Security Audit Logs
            Route::get('/audit-logs', [AuditLogController::class, 'index']);

            // Specialized Catalog & Product Management
            Route::get('/products/export', [ProductController::class, 'export']);
            Route::post('/products/import', [ProductController::class, 'import']);
            Route::apiResource('products', ProductController::class);

            // Specialized Order Management
            Route::get('/orders', [OrderController::class, 'index']);
            Route::get('/orders/{id}', [OrderController::class, 'show']);
            Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
            Route::get('/orders/{id}/invoice', [OrderController::class, 'downloadInvoice']);

            // Specialized Inventory Management
            Route::get('/inventory', [InventoryController::class, 'index']);
            Route::post('/inventory/adjust', [InventoryController::class, 'adjustStock']);
            Route::get('/inventory/movements', [InventoryController::class, 'movements']);

            // Media Library
            Route::get('/media', [MediaController::class, 'index']);
            Route::post('/media/upload', [MediaController::class, 'upload']);
            Route::post('/media/{id}/optimize', [MediaController::class, 'optimize']);
            Route::delete('/media/{id}', [MediaController::class, 'destroy']);

            // Homepage Builder
            Route::get('/homepage-builder', [HomepageBuilderController::class, 'index']);
            Route::post('/homepage-builder', [HomepageBuilderController::class, 'store']);
            Route::put('/homepage-builder/{id}', [HomepageBuilderController::class, 'update']);
            Route::post('/homepage-builder/reorder', [HomepageBuilderController::class, 'reorder']);
            Route::delete('/homepage-builder/{id}', [HomepageBuilderController::class, 'destroy']);

            // System Settings
            Route::get('/settings', [SettingsController::class, 'index']);
            Route::post('/settings', [SettingsController::class, 'update']);

            // RBAC (Users & Roles)
            Route::apiResource('users', UserController::class);
            Route::apiResource('roles', RoleController::class);

            // Generic Resource Registry CRUD
            Route::get('/resources/{resource}', [ResourceController::class, 'index']);
            Route::post('/resources/{resource}', [ResourceController::class, 'store']);
            Route::get('/resources/{resource}/{id}', [ResourceController::class, 'show']);
            Route::put('/resources/{resource}/{id}', [ResourceController::class, 'update']);
            Route::delete('/resources/{resource}/{id}', [ResourceController::class, 'destroy']);
        });
    });
});
