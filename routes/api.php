<?php

use App\Http\Controllers\Api\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Api\Admin\SupportController as AdminSupportController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentGatewayController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\SearchHistoryController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\SupportController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Public catalog
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/search', [HomeController::class, 'search']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::get('/categories/{category}/products', [CategoryController::class, 'products']);
    Route::get('/brands', [BrandController::class, 'index']);
    Route::get('/brands/{brand}', [BrandController::class, 'show']);
    Route::get('/brands/{brand}/products', [BrandController::class, 'products']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/products/{product}/ratings', [RatingController::class, 'index']);
    Route::get('/delivery-slots', [OrderController::class, 'deliverySlots']);
    Route::get('/subscription-plans', [SubscriptionController::class, 'plans']);
    Route::get('/faqs', [SupportController::class, 'faqs']);

    // Payment gateway — PayPal (public)
    Route::get('/payments/paypal/success/{payment}', [PaymentGatewayController::class, 'paypalSuccess'])
        ->name('payments.paypal.success');
    Route::get('/payments/paypal/cancel/{payment}', [PaymentGatewayController::class, 'paypalCancel'])
        ->name('payments.paypal.cancel');
    Route::post('/payments/paypal/webhook', [PaymentGatewayController::class, 'paypalWebhook'])
        ->name('payments.paypal.webhook');
    Route::get('/payments/mock/{payment}/checkout', [PaymentGatewayController::class, 'mockCheckout']);
    Route::post('/payments/mock/{payment}/complete', [PaymentGatewayController::class, 'mockComplete']);

    // Authenticated customer routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Profile
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/image', [ProfileController::class, 'uploadImage']);
        Route::put('/profile/password', [ProfileController::class, 'changePassword']);

        // Search history
        Route::get('/search-history', [SearchHistoryController::class, 'index']);
        Route::delete('/search-history/{searchHistory}', [SearchHistoryController::class, 'destroy']);
        Route::delete('/search-history', [SearchHistoryController::class, 'clear']);

        // Cart
        Route::get('/cart', [CartController::class, 'show']);
        Route::post('/cart/items', [CartController::class, 'addItem']);
        Route::put('/cart/items/{itemId}', [CartController::class, 'updateItem']);
        Route::delete('/cart/items/{itemId}', [CartController::class, 'removeItem']);
        Route::delete('/cart', [CartController::class, 'clear']);

        // Orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);

        // Payments
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);
        Route::post('/orders/{order}/payment/retry', [PaymentGatewayController::class, 'retry']);

        // Favorites
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('/favorites', [FavoriteController::class, 'store']);
        Route::delete('/favorites/{product}', [FavoriteController::class, 'destroy']);

        // Ratings
        Route::post('/products/{product}/ratings', [RatingController::class, 'store']);
        Route::put('/ratings/{rating}', [RatingController::class, 'update']);
        Route::delete('/ratings/{rating}', [RatingController::class, 'destroy']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

        // Subscriptions
        Route::get('/subscriptions', [SubscriptionController::class, 'index']);
        Route::post('/subscriptions', [SubscriptionController::class, 'subscribe']);
        Route::post('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel']);

        // Support
        Route::get('/support/tickets', [SupportController::class, 'index']);
        Route::post('/support/tickets', [SupportController::class, 'store']);
        Route::get('/support/tickets/{ticket}', [SupportController::class, 'show']);
    });

    // Admin routes
    Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::apiResource('users', AdminUserController::class);
        Route::apiResource('categories', AdminCategoryController::class);
        Route::apiResource('brands', AdminBrandController::class);
        Route::apiResource('products', AdminProductController::class);

        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
        Route::put('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
        Route::post('/orders/{order}/refund', [AdminOrderController::class, 'refund']);

        Route::get('/payments', [AdminPaymentController::class, 'index']);
        Route::get('/payments/revenue', [AdminPaymentController::class, 'revenue']);

        Route::get('/notifications', [AdminNotificationController::class, 'index']);
        Route::post('/notifications', [AdminNotificationController::class, 'store']);

        Route::get('/subscription-plans', [AdminSubscriptionController::class, 'plans']);
        Route::post('/subscription-plans', [AdminSubscriptionController::class, 'storePlan']);
        Route::put('/subscription-plans/{plan}', [AdminSubscriptionController::class, 'updatePlan']);
        Route::get('/subscribers', [AdminSubscriptionController::class, 'subscribers']);

        Route::get('/support/tickets', [AdminSupportController::class, 'index']);
        Route::get('/support/tickets/{ticket}', [AdminSupportController::class, 'show']);
        Route::post('/support/tickets/{ticket}/reply', [AdminSupportController::class, 'reply']);
        Route::post('/support/tickets/{ticket}/close', [AdminSupportController::class, 'close']);
    });
});
