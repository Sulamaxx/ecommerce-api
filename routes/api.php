<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\{
    AuthController,
    ProductController,
    CartController,
    OrderController,
    ContactController,
    StaffController,
    UserController
};

Route::prefix('v2')->group(function () {

    Route::get('/debug-check', function () {
        return response()->json(['status' => 'api.php loaded']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });

    // 🛡️ Auth Routes
    Route::post('/register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'store'])->name('register');
    Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])->middleware('guest')->name('login');
    Route::post('/forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])->middleware('guest')->name('password.email');
    Route::post('/reset-password', [App\Http\Controllers\Auth\NewPasswordController::class, 'store'])->middleware('guest')->name('password.store');
    Route::get('/verify-email/{id}/{hash}', App\Http\Controllers\Auth\VerifyEmailController::class)->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])->middleware(['auth', 'throttle:6,1'])->name('verification.send');

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // 🔓 Public Routes
    Route::post('/products/featured', [ProductController::class, 'featured']);
    Route::post('/products/filtered_products', [ProductController::class, 'getFilteredProducts']);
    Route::get('/product/{product_id}', [ProductController::class, 'show']);
    Route::get('/contact/sendMessage', [ContactController::class, 'send']);


    // 🔒 Protected Routes (Requires Sanctum Token)
    Route::middleware('auth:sanctum')->group(function () {
        // User routes
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::put('/', [UserController::class, 'update']);
            Route::delete('/{id}', [UserController::class, 'destroy']);
        });

        // User Info
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::put('/order/{id}', [OrderController::class, 'updateOrder']);

        // Cart
        Route::post('/cart/add', [CartController::class, 'addToCart']);
        Route::get('/cart', [CartController::class, 'getCartItems']);
        Route::put('/cart/{id}', [CartController::class, 'updateCartItem']);
        Route::delete('/cart/{id}', [CartController::class, 'deleteCartItem']);
        // Route::delete('/cart', [CartController::class, 'clearCart']);

        // Checkout
        Route::post('/checkout', [OrderController::class, 'saveOrder']);
        // Route::post('/checkout/details', [OrderController::class, 'saveDetails']);
        // Route::post('/checkout/payment', [OrderController::class, 'makePayment']);

    });

    Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
        // Admin/Advanced
        Route::get('/orders/history', [OrderController::class, 'getAllOrderHistory']);
        Route::post('/admin/dashboard', [OrderController::class, 'getDashboardData']);

        Route::get('/admin/users/paginated/', [UserController::class, 'getPaginatedUsers']);
        Route::get('/admin/staff/paginated/', [StaffController::class, 'getPaginatedStaff']);
        Route::post('/staff', [StaffController::class, 'store']);
        Route::put('/staff/{id}', [StaffController::class, 'update']);

        Route::post('/products/add_new_product', [ProductController::class, 'store']);
        Route::put('/products/{product_id}', [ProductController::class, 'update']);
    });
    
    Route::middleware(['auth:sanctum', 'is_admin_or_staff'])->group(function () {
        Route::post('/admin/all_products', [ProductController::class, 'getPaginatedProducts']);
        Route::get('/orders/all', [OrderController::class, 'getPaginatedOrderDetails']);
    });
});
