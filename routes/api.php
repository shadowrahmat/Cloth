<?php

use App\Http\Controllers\Api\V1\Admin\AppearanceController;
use App\Http\Controllers\Api\V1\Admin\AuditLogController;
use App\Http\Controllers\Api\V1\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\V1\Admin\BlogController;
use App\Http\Controllers\Api\V1\Admin\CouponController;
use App\Http\Controllers\Api\V1\Admin\CustomerController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\EmailTemplateController;
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
use App\Http\Controllers\Api\V1\Customer\CustomerReturnController;
use App\Http\Controllers\Api\V1\Public\BlogController as PublicBlogController;
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
        Route::get('/products/facets', [PublicController::class, 'productFacets']);
        Route::get('/products/{slug}', [PublicController::class, 'productDetail']);
        Route::post('/products/{id}/reviews', [PublicController::class, 'addReview'])->middleware('throttle:5,1');
        Route::get('/search', [PublicController::class, 'search']);
        Route::get('/collections', [PublicController::class, 'collections']);
        Route::get('/collections/{slug}', [PublicController::class, 'collectionDetail']);
        Route::get('/pages/{slug}', [PublicController::class, 'page']);
        Route::get('/blog', [PublicBlogController::class, 'index']);
        Route::get('/blog/{slug}', [PublicBlogController::class, 'show']);
        Route::post('/contact', [PublicController::class, 'submitContact']);
        Route::post('/newsletter/subscribe', [PublicController::class, 'subscribeNewsletter'])->middleware('throttle:5,1');
        Route::get('/settings', [PublicController::class, 'settings']);
        Route::get('/site', [PublicController::class, 'site']);
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
        // 10 attempts per minute per IP to slow down credential stuffing.
        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/register', [CustomerAuthController::class, 'register']);
            Route::post('/login', [CustomerAuthController::class, 'login']);
        });

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [CustomerAuthController::class, 'me']);
            Route::put('/profile', [CustomerAuthController::class, 'updateProfile']);
            Route::post('/logout', [CustomerAuthController::class, 'logout']);

            Route::get('/orders', [CustomerOrderController::class, 'orders']);
            Route::get('/orders/{orderNumber}', [CustomerOrderController::class, 'orderDetail']);
            Route::post('/orders/{orderNumber}/returns', [CustomerReturnController::class, 'store']);
            Route::get('/returns', [CustomerReturnController::class, 'index']);
            Route::get('/addresses', [CustomerOrderController::class, 'getAddresses']);
            Route::post('/addresses', [CustomerOrderController::class, 'storeAddress']);
            Route::delete('/addresses/{id}', [CustomerOrderController::class, 'deleteAddress']);
        });
    });

    // -------------------------------------------------------------
    // Admin API: /api/v1/admin
    // -------------------------------------------------------------
    Route::prefix('admin')->group(function () {
        Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');

        // Every route below needs a signed-in admin or manager (`admin`), and then the
        // role permission for its module (`permission:`). With a module name the action
        // follows the HTTP method (GET view, POST create, PUT update, DELETE delete);
        // `permission:update_x` is used where the method alone is not enough.
        Route::middleware(['auth:sanctum', 'admin'])->group(function () {
            Route::get('/me', [AdminAuthController::class, 'me']);
            Route::post('/logout', [AdminAuthController::class, 'logout']);

            // Dashboard & Badges (any admin or manager)
            Route::get('/dashboard', [DashboardController::class, 'index']);
            Route::get('/dashboard/badges', [DashboardController::class, 'badges']);
            Route::get('/search', [DashboardController::class, 'search']);

            // Reports & Analytics
            Route::middleware('permission:view_orders')->group(function () {
                Route::get('/reports', [ReportController::class, 'index']);
                Route::get('/reports/sales', [ReportController::class, 'sales']);
            });

            // Customers, inquiries and newsletter
            Route::middleware('permission:customers')->group(function () {
                Route::get('/customers', [CustomerController::class, 'index']);
                Route::get('/customers/{id}', [CustomerController::class, 'show']);

                Route::get('/messages', [MessageController::class, 'index']);
                Route::put('/messages/{id}', [MessageController::class, 'update']);
                Route::delete('/messages/{id}', [MessageController::class, 'destroy']);

                // Newsletter subscribers as a CSV (for Mailchimp etc.)
                Route::get('/newsletter-subscribers/export', function () {
                    $rows = \App\Models\NewsletterSubscriber::orderByDesc('subscribed_at')->get(['email', 'is_active', 'subscribed_at']);

                    return response()->streamDownload(function () use ($rows) {
                        $out = fopen('php://output', 'w');
                        fputcsv($out, ['email', 'status', 'subscribed_at']);
                        foreach ($rows as $r) {
                            fputcsv($out, [$r->email, $r->is_active ? 'subscribed' : 'unsubscribed', optional($r->subscribed_at)->toDateTimeString()]);
                        }
                        fclose($out);
                    }, 'newsletter-subscribers-' . date('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
                });
            });

            // Orders, coupons, returns, logistics
            Route::middleware('permission:orders')->group(function () {
                Route::apiResource('coupons', CouponController::class);

                Route::get('/returns', [ReturnController::class, 'index']);
                Route::put('/returns/{id}', [ReturnController::class, 'update']);

                // Logistics & Courier (Steadfast)
                Route::get('/logistics', [LogisticsController::class, 'index']);
                Route::post('/logistics/consignment', [LogisticsController::class, 'createConsignment']);
                Route::get('/logistics/{id}/track', [LogisticsController::class, 'trackStatus']);

                Route::get('/orders', [OrderController::class, 'index']);
                Route::get('/orders/{id}', [OrderController::class, 'show']);
                Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
                Route::get('/orders/{id}/invoice', [OrderController::class, 'downloadInvoice']);
            });

            // Products & reviews
            Route::middleware('permission:products')->group(function () {
                Route::get('/reviews', [ReviewController::class, 'index']);
                Route::put('/reviews/{id}/toggle-approval', [ReviewController::class, 'toggleApproval']);
                Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

                Route::get('/products/export', [ProductController::class, 'export']);
                Route::post('/products/import', [ProductController::class, 'import']);
                Route::post('/products/{id}/duplicate', [ProductController::class, 'duplicate']);
                Route::apiResource('products', ProductController::class);
            });

            // Bulk status change / delete: the permission depends on the action, checked in the controller
            Route::post('/products/bulk', [ProductController::class, 'bulk']);

            // Categories Management (ResourceController checks the `categories` permission itself)
            Route::get('/categories', function (\Illuminate\Http\Request $request) {
                return app(ResourceController::class)->index($request, 'categories');
            });
            Route::post('/categories', function (\Illuminate\Http\Request $request) {
                return app(ResourceController::class)->store($request, 'categories');
            });
            Route::get('/categories/{id}', function (\Illuminate\Http\Request $request, $id) {
                return app(ResourceController::class)->show($request, 'categories', $id);
            });
            Route::put('/categories/{id}', function (\Illuminate\Http\Request $request, $id) {
                return app(ResourceController::class)->update($request, 'categories', $id);
            });
            Route::delete('/categories/{id}', function (\Illuminate\Http\Request $request, $id) {
                return app(ResourceController::class)->destroy($request, 'categories', $id);
            });

            // Inventory
            Route::middleware('permission:inventory')->group(function () {
                Route::get('/inventory', [InventoryController::class, 'index']);
                Route::get('/inventory/movements', [InventoryController::class, 'movements']);
            });
            Route::post('/inventory/adjust', [InventoryController::class, 'adjustStock'])->middleware('permission:update_inventory');

            // Media Library
            Route::middleware('permission:media')->group(function () {
                Route::get('/media', [MediaController::class, 'index']);
                Route::post('/media/upload', [MediaController::class, 'upload']);
                Route::delete('/media/{id}', [MediaController::class, 'destroy']);
            });
            Route::post('/media/{id}/optimize', [MediaController::class, 'optimize'])->middleware('permission:update_media');

            // Content: blog, homepage builder, site appearance
            Route::middleware('permission:cms')->group(function () {
                Route::apiResource('blog', BlogController::class);

                Route::get('/homepage-builder', [HomepageBuilderController::class, 'index']);
                Route::post('/homepage-builder', [HomepageBuilderController::class, 'store']);
                Route::put('/homepage-builder/{id}', [HomepageBuilderController::class, 'update']);
                Route::delete('/homepage-builder/{id}', [HomepageBuilderController::class, 'destroy']);

                // Site Customize (logo, header, footer, contact, SEO)
                Route::get('/appearance', [AppearanceController::class, 'show']);
                Route::put('/appearance', [AppearanceController::class, 'update']);
            });
            Route::middleware('permission:update_cms')->group(function () {
                Route::post('/homepage-builder/reorder', [HomepageBuilderController::class, 'reorder']);
                Route::post('/appearance/reset', [AppearanceController::class, 'reset']);
            });

            // System settings, email templates and audit logs
            Route::middleware('permission:view_settings')->group(function () {
                Route::get('/settings', [SettingsController::class, 'index']);
                Route::get('/email-templates', [EmailTemplateController::class, 'index']);
                Route::get('/email-logs', [EmailTemplateController::class, 'logs']);
                Route::get('/audit-logs', [AuditLogController::class, 'index']);
            });
            Route::middleware('permission:update_settings')->group(function () {
                Route::post('/settings', [SettingsController::class, 'update']);
                Route::put('/email-templates/{key}', [EmailTemplateController::class, 'update']);
                Route::post('/email-templates/{key}/preview', [EmailTemplateController::class, 'preview']);
                Route::post('/email-templates/{key}/test', [EmailTemplateController::class, 'test'])->middleware('throttle:10,1');
                Route::post('/email-templates/{key}/reset', [EmailTemplateController::class, 'reset']);
            });

            // Admins only: users, roles and mail credentials, so a manager can't grant themselves admin.
            Route::middleware('admin:full')->group(function () {
                Route::get('/email-settings', [EmailTemplateController::class, 'settings']);
                Route::put('/email-settings', [EmailTemplateController::class, 'saveSettings']);
                Route::apiResource('users', UserController::class);
                Route::apiResource('roles', RoleController::class);
            });

            // Generic Resource Registry CRUD (ResourceController checks the permission per resource)
            Route::get('/resources/{resource}', [ResourceController::class, 'index']);
            Route::post('/resources/{resource}', [ResourceController::class, 'store']);
            Route::get('/resources/{resource}/{id}', [ResourceController::class, 'show']);
            Route::put('/resources/{resource}/{id}', [ResourceController::class, 'update']);
            Route::delete('/resources/{resource}/{id}', [ResourceController::class, 'destroy']);
        });
    });
});
