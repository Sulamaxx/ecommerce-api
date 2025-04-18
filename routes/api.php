<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\{
    AuthController,
    ProductController,
    CartController,
    OrderController,
    ContactController
};

Route::prefix('v2')->group(function () {

    Route::get('/debug-check', function () {
        return response()->json(['status' => 'api.php loaded']);
    });

    // 🛡️ Auth Routes
    Route::post('/register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'store'])->name('register');

    Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])->middleware('guest')->name('login');
    Route::post('/forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])->middleware('guest')->name('password.email');
    Route::post('/reset-password', [App\Http\Controllers\Auth\NewPasswordController::class, 'store'])->middleware('guest')->name('password.store');
    Route::get('/verify-email/{id}/{hash}', App\Http\Controllers\Auth\VerifyEmailController::class)->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])->middleware(['auth', 'throttle:6,1'])->name('verification.send');
    Route::post('/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

    // 🔓 Public Routes
    Route::post('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/accessories', [ProductController::class, 'accessories']);
    Route::get('/cart/{user_id}', [CartController::class, 'show']);
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/contact/sendMessage', [ContactController::class, 'send']);

    // 🔒 Protected Routes (Requires Sanctum Token)
    Route::middleware('auth:sanctum')->group(function () {

        // User Info
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // Products
        Route::post('/products/beard', [ProductController::class, 'beard']);
        Route::post('/products/hair', [ProductController::class, 'hair']);

        // Cart
        Route::post('/cart/add', [CartController::class, 'add']);

        // Checkout
        Route::post('/checkout/details', [OrderController::class, 'saveDetails']);
        Route::post('/checkout/payment', [OrderController::class, 'makePayment']);

        // Admin/Advanced
        Route::post('/users/list', [AuthController::class, 'listUsers']);
        Route::post('/orders/history', [OrderController::class, 'orderHistory']);
    });

});
