<?php
use App\Http\Controllers\API\{
  AuthController,
  ProductController,
  CartController,
  OrderController,
  DashboardController,
  ContactController
};
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Public Routes
Route::post('/featuredProducts', [ProductController::class, 'featured']);
Route::get('/products/accessories', [ProductController::class, 'accessories']);
Route::get('/cart/{user_id}', [CartController::class, 'show']);
Route::post('/checkout', [OrderController::class, 'checkout']);
Route::get('/contact/sendMessage', [ContactController::class, 'send']);

// Routes that require auth
Route::middleware('auth:sanctum')->group(function () {
  Route::post('/products/beard', [ProductController::class, 'beard']);
  Route::post('/products/hair', [ProductController::class, 'hair']);

  Route::post('/cart/add', [CartController::class, 'add']);
  Route::post('/checkout/details', [OrderController::class, 'saveDetails']);
  Route::post('/checkout/payment', [OrderController::class, 'makePayment']);

  Route::post('/users/list', [AuthController::class, 'listUsers']);
  Route::post('/orders/history', [OrderController::class, 'orderHistory']);

  // Admin-only routes
  Route::get('/admin/dashboard/orderSummary', [DashboardController::class, 'orderSummary']);
});
?>