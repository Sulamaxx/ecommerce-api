<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Mail\OrderConfirmationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function payhereCheckout(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'shipping_address' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'apartment' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
            'shipping_rate' => 'required|numeric',
            'tax' => 'required|numeric',
            'discount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get user cart items
        $userId = auth()->id();
        $cartItems = Cart::where('user_id', $userId)
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 400);
        }

        try {
            DB::beginTransaction();

            // Check product stock before proceeding
            foreach ($cartItems as $item) {
                if ($item->product->stock < $item->quantity) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Insufficient stock for product: ' . $item->product->name,
                        'product_id' => $item->product_id,
                        'requested' => $item->quantity,
                        'available' => $item->product->stock
                    ], 400);
                }
            }

            // Calculate order total
            $total = 0;
            $totalItemDiscount = 0;
            foreach ($cartItems as $item) {
                $item_price = $item->product->price;
                $discount_percentage = $item->product->discount;
                $discount_amount = ($item_price * $discount_percentage) / 100;
                $discounted_price = $item_price - $discount_amount;

                // Add to running totals
                $total += $discounted_price * $item->quantity;
                $totalItemDiscount += $discount_amount * $item->quantity;
            }

            $total = $total + $request->tax + $request->shipping_rate;

            // Create the order
            $order = Order::create([
                'user_id' => $userId,
                'shipping_address' => $request->shipping_address,
                'payment_method' => 'PayHere',
                'status' => 'pending',
                'total' => $total,
                'discount' => $totalItemDiscount,
                'tax' => $request->tax,
                'shipping_rate' => $request->shipping_rate,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'country' => $request->country,
                'company' => $request->company,
                'address' => $request->address,
                'apartment' => $request->apartment,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'phone' => $request->phone
            ]);

            // Add items to order_items
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'total' => ($item->product->price - ($item->product->price * $item->product->discount / 100)) * $item->quantity,
                    'discount' => ($item->product->price * $item->product->discount / 100) * $item->quantity,
                ]);
            }

            DB::commit();

            $user = auth()->user();

            $payhere_data = [
                "sandbox" => config('payhere.mode') === 'sandbox',
                "merchant_id" => config('payhere.merchant_id'),
                "return_url" => route('payhere.return'),
                "cancel_url" => config('app.frontend_url', url('/')),
                "notify_url" => url('/api/v2/payhere/notify'),
                "order_id" => $order->id,
                "items" => "Order " . $order->id,
                "amount" => $order->total,
                "currency" => "LKR",
                "first_name" => $order->first_name,
                "last_name" => $order->last_name,
                "email" => $user->email,
                "phone" => $order->phone,
                "address" => $order->address,
                "city" => $order->city,
                "country" => $order->country,
            ];

            // --- Temporary Debug Logging ---
            Log::info('PayHere Checkout Request Data:', $payhere_data);
            // --------------------------

            return response()->json([
                'message' => 'Order created, proceed to payment.',
                'order' => $order->load('orderItems'),
                'payhere' => $payhere_data
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Save a new order from the user's cart
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Save a new order from the user's cart
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveOrder(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'shipping_address' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'apartment' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
            'shipping_rate' => 'required|numeric',
            'tax' => 'required|numeric',
            'discount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get user cart items
        $userId = auth()->id();
        $cartItems = Cart::where('user_id', $userId)
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 400);
        }

        try {
            DB::beginTransaction();

            // Check product stock before proceeding
            foreach ($cartItems as $item) {
                if ($item->product->stock < $item->quantity) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Insufficient stock for product: ' . $item->product->name,
                        'product_id' => $item->product_id,
                        'requested' => $item->quantity,
                        'available' => $item->product->stock
                    ], 400);
                }
            }

            // Calculate order total
            $total = 0;
            $totalItemDiscount = 0;
            foreach ($cartItems as $item) {
                $item_price = $item->product->price;
                $discount_percentage = $item->product->discount;
                $discount_amount = ($item_price * $discount_percentage) / 100;
                $discounted_price = $item_price - $discount_amount;

                // Add to running totals
                $total += $discounted_price * $item->quantity;
                $totalItemDiscount += $discount_amount * $item->quantity;
            }

            // Apply discount, tax and shipping
            // $total = $total - $request->discount + $request->tax + $request->shipping_rate; //For Security purposes keep commented (No addtional discount for now from frontend)
            $total = $total + $request->tax + $request->shipping_rate;

            // Create the order
            $order = Order::create([
                'user_id' => $userId,
                'shipping_address' => $request->shipping_address,
                'payment_method' => $request->payment_method,
                'status' => 'processing',
                'total' => $total,
                // 'discount' => $request->discount + $totalItemDiscount, //Not accept discount from front for now
                'discount' => $totalItemDiscount,
                'tax' => $request->tax,
                'shipping_rate' => $request->shipping_rate,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'country' => $request->country,
                'company' => $request->company,
                'address' => $request->address,
                'apartment' => $request->apartment,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'phone' => $request->phone
            ]);

            // Add items to order_items and deduct stock
            foreach ($cartItems as $item) {
                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'total' => ($item->product->price - ($item->product->price * $item->product->discount / 100)) * $item->quantity,
                    'discount' => ($item->product->price * $item->product->discount / 100) * $item->quantity,
                ]);

                // Deduct stock from product
                $product = Product::find($item->product_id);
                $product->stock = $product->stock - $item->quantity;
                $product->save();
            }

            // Clear the user's cart
            Cart::where('user_id', $userId)->delete();

            DB::commit();

            // Send order confirmation email
            $this->sendOrderConfirmationEmail($order);

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load('orderItems')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send order confirmation email to the customer
     * 
     * @param Order $order
     * @return void
     */
    private function sendOrderConfirmationEmail(Order $order)
    {
        try {
            // Refresh order with all necessary relationships for the email
            $order = Order::with([
                'orderItems.product', // Load order items with their products
                'user'                // Load the user
            ])->find($order->id);

            // Get user email from the user model
            $user = User::find($order->user_id);
            if (!$user) {
                Log::error('Failed to send order confirmation email: User not found', [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id
                ]);
                return;
            }

            // Send the email
            Mail::to($user->email)
                ->send(new OrderConfirmationMail($order));

            Log::info('Order confirmation email sent successfully', [
                'order_id' => $order->id,
                'user_email' => $user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update an existing order
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Check if user owns this order
        if (auth()->id() !== $order->user_id && auth()->user()->user_type !== 'admin' && auth()->user()->user_type !== 'staff') {
            return response()->json(['message' => 'Unathorized  Access! Order Owner, Admin or Staff only can update the order.'], 403);
        }

        // Validate request data
        $validator = Validator::make($request->all(), [
            'payment_method' => 'sometimes|string',
            'status' => 'sometimes|string|in:processing,shipped,delivered,canceled',
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'country' => 'sometimes|string|max:255',
            'company' => 'nullable|string|max:255',
            'address' => 'sometimes|string|max:255',
            'shipping_address' => 'sometimes|string|max:255',
            'apartment' => 'nullable|string|max:255',
            'city' => 'sometimes|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'sometimes|string|max:20',
            'phone' => 'sometimes|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Handle cancellation - restore product stock if order is being canceled
            if ($request->has('status') && $request->status == 'canceled' && $order->status != 'canceled') {
                $orderItems = $order->orderItems;
                foreach ($orderItems as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->stock = $product->stock + $item->quantity;
                        $product->save();
                    }
                }
            }

            // Update order fields
            if ($request->has('payment_method')) {
                $order->payment_method = $request->payment_method;
            }
            if ($request->has('status')) {
                $order->status = $request->status;
            }
            if ($request->has('first_name')) {
                $order->first_name = $request->first_name;
            }
            if ($request->has('last_name')) {
                $order->last_name = $request->last_name;
            }
            if ($request->has('country')) {
                $order->country = $request->country;
            }
            if ($request->has('address')) {
                $order->address = $request->address;
            }
            if ($request->has('shipping_address')) {
                $order->shipping_address = $request->shipping_address;
            }
            if ($request->has('apartment')) {
                $order->apartment = $request->apartment;
            }
            if ($request->has('city')) {
                $order->city = $request->city;
            }
            if ($request->has('state')) {
                $order->state = $request->state;
            }
            if ($request->has('postal_code')) {
                $order->postal_code = $request->postal_code;
            }
            if ($request->has('phone')) {
                $order->phone = $request->phone;
            }

            $order->save();

            DB::commit();

            return response()->json([
                'message' => 'Order updated successfully',
                'order' => $order->load('orderItems')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get paginated order history for the authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderHistoryByUser(Request $request)
    {
        try {
            // Default to page 1 if not specified
            $page = $request->input('page', 1);
            // Fixed items per page to 10
            $perPage = 10;

            // Start with a base query for the current user's orders
            $userId = auth()->id();
            $query = Order::where('user_id', $userId);

            // Apply search filter if provided
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('id', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('status', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('first_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('postal_code', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Apply status filter if provided
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            // Sort by created_at in descending order (latest first)
            $query->orderBy('created_at', 'desc');

            // Get the total count for pagination
            $totalOrders = $query->count();
            $totalPages = ceil($totalOrders / $perPage);

            // Get orders for current page
            $orders = $query->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            // Format the orders for the frontend
            $formattedOrders = $orders->map(function ($order) {
                return [
                    'id' => str_pad($order->id, 4, '0', STR_PAD_LEFT),
                    'total' => number_format($order->total, 2),
                    'date' => $order->created_at->format('M jS, Y'),
                    'status' => ucfirst($order->status)
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'orders' => $formattedOrders,
                    'pagination' => [
                        'currentPage' => (int) $page,
                        'totalPages' => $totalPages,
                        'perPage' => $perPage,
                        'totalOrders' => $totalOrders
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve order history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get paginated order history for the authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllOrderHistory(Request $request)
    {
        try {
            // Default to page 1 if not specified
            $page = $request->input('page', 1);
            // Fixed items per page to 10
            $perPage = 10;

            $query = Order::query();

            // Sort by created_at in descending order (latest first)
            $query->orderBy('created_at', 'desc');

            // Get the total count for pagination
            $totalOrders = $query->count();
            $totalPages = ceil($totalOrders / $perPage);

            // Get orders for current page
            $orders = $query->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            // Format the orders for the frontend
            $formattedOrders = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'total' => number_format($order->total, 2),
                    'date' => $order->created_at->format('M jS, Y'),
                    'status' => ucfirst($order->status)
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'orders' => $formattedOrders,
                    'pagination' => [
                        'currentPage' => (int) $page,
                        'totalPages' => $totalPages,
                        'perPage' => $perPage,
                        'totalOrders' => $totalOrders
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve order history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get paginated detailed orders
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaginatedOrderDetails(Request $request)
    {
        try {
            // Default to page 1 if not specified
            $page = $request->input('page', 1);
            // Fixed items per page
            $perPage = 5;

            // Start with a base query - admins see all orders, users see only their own
            $query = Order::with(['orderItems.product', 'user']);

            // if (!auth()->user()->isAdmin()) {
            //     $query->where('user_id', auth()->id());
            // }

            // Apply search filter if provided
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('id', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('status', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('first_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('city', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('country', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Apply status filter if provided
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            // Apply date range filter if provided
            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Sort by created_at in descending order (latest first)
            $query->orderBy('created_at', 'desc');

            // Get the total count for pagination
            $totalOrders = $query->count();
            $totalPages = ceil($totalOrders / $perPage);

            // Get orders for current page
            $orders = $query->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            // Format the orders with detailed information
            $formattedOrders = $orders->map(function ($order) {
                return [
                    'id' => (string) $order->id,
                    'date' => $order->created_at->format('M d, Y'),
                    'status' => $order->status,
                    'customer' => [
                        'fullName' => $order->first_name . ' ' . $order->last_name,
                        'email' => $order->user->email,
                        'phone' => $order->user->mobile
                    ],
                    'orderInfo' => [
                        'shipping' => 'Next express',
                        'paymentMethod' => $order->payment_method,
                        'status' => $order->status
                    ],
                    'deliverTo' => [
                        'address' => $this->formatFullAddress($order)
                    ],
                    'paymentInfo' => [
                        // 'cardType' => $this->determineCardType($order->payment_method),
                        // 'cardNumber' => '**** **** ' . substr($order->payment_method_details ?? '0000', -4),
                        'paymentMethod' => $order->payment_method,
                        'businessName' => $order->company ?: ($order->first_name . ' ' . $order->last_name),
                        'phone' => $order->phone
                    ],
                    'products' => $order->orderItems->map(function ($item) use ($order) {
                        return [
                            'name' => $item->product->name,
                            'orderId' => '#' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                            'quantity' => $item->quantity,
                            'total' => 'LKR ' . number_format($item->total, 2)
                        ];
                    }),
                    'summary' => [
                        'subtotal' => 'LKR ' . number_format($order->total - $order->tax - $order->shipping_rate + $order->discount, 2),
                        'tax' => 'LKR ' . number_format($order->tax, 2),
                        'discount' => 'LKR ' . number_format($order->discount, 2),
                        'shipping' => 'LKR ' . number_format($order->shipping_rate, 2),
                        'total' => 'LKR ' . number_format($order->total, 2)
                    ]
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'orders' => $formattedOrders,
                    'pagination' => [
                        'currentPage' => (int) $page,
                        'totalPages' => $totalPages,
                        'perPage' => $perPage,
                        'totalOrders' => $totalOrders
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve order details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to format full address
     *
     * @param Order $order
     * @return string
     */
    private function formatFullAddress(Order $order)
    {
        $addressParts = [];

        if ($order->address) {
            $addressParts[] = $order->address;
        }

        if ($order->apartment) {
            $addressParts[] = $order->apartment;
        }

        if ($order->city) {
            $addressParts[] = $order->city;
        }

        if ($order->state) {
            $addressParts[] = $order->state;
        }

        if ($order->country) {
            $addressParts[] = $order->country;
        }

        if ($order->postal_code) {
            $addressParts[] = $order->postal_code;
        }

        return implode(', ', $addressParts);
    }

    /**
     * Helper method to determine card type
     *
     * @param string $paymentMethod
     * @return string
     */
    private function determineCardType($paymentMethod)
    {
        // This is a simplified example - you would implement your own logic
        // based on your payment gateway's data structure
        if (stripos($paymentMethod, 'visa') !== false) {
            return 'Visa';
        } elseif (stripos($paymentMethod, 'mastercard') !== false || stripos($paymentMethod, 'master') !== false) {
            return 'Master Card';
        } elseif (stripos($paymentMethod, 'amex') !== false || stripos($paymentMethod, 'american express') !== false) {
            return 'American Express';
        }

        return 'Credit Card'; // Default
    }

    /**
     * Get dashboard data including sales statistics, best selling products, and recent orders
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardData(Request $request)
    {
        try {
            // Get the time period from request, default to monthly
            $period = $request->input('period', 'monthly');

            // Get dashboard stats
            $stats = $this->getDashboardStats();

            // Get sales graph data based on selected period
            $salesData = $this->getSalesGraphData($period);

            // Get best selling products
            $bestSellingProducts = $this->getBestSellingProducts();

            // Get 6 most recent orders
            $recentOrders = $this->getRecentOrders();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'stats' => $stats,
                    'sales_graph' => $salesData,
                    'best_selling_products' => $bestSellingProducts,
                    'recent_orders' => $recentOrders
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard statistics (order counts by status) along with date range
     *
     * @return array
     */
    private function getDashboardStats()
    {
        $totalOrders = Order::count();
        $activeOrders = Order::whereIn('status', ['processing', 'shipped'])->count();
        $completedOrders = Order::where('status', 'delivered')->count();
        $returnedOrders = Order::where('status', 'canceled')->count();

        // Get the minimum (first) and maximum (latest) order dates
        $firstOrderDate = Order::min('created_at');
        $latestOrderDate = Order::max('created_at');

        return [
            'total_orders' => $totalOrders,
            'active_orders' => $activeOrders,
            'completed_orders' => $completedOrders,
            'returned_orders' => $returnedOrders,
            'start_date' => $firstOrderDate ? date('M d, Y', strtotime($firstOrderDate)) : null,
            'end_date' => $latestOrderDate ? date('M d, Y', strtotime($latestOrderDate)) : null
        ];
    }

    /**
     * Get sales graph data based on selected period
     *
     * @param string $period
     * @return array
     */
    private function getSalesGraphData($period)
    {
        $today = now();
        $salesData = [];

        switch ($period) {
            case 'weekly':
                // Last 7 days
                $startDate = $today->copy()->subDays(6)->startOfDay();
                $endDate = $today->copy()->endOfDay();

                $dailySales = Order::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total) as total_sales')
                )
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                // Create array with all 7 days
                $currentDate = $startDate->copy();
                while ($currentDate <= $endDate) {
                    $dateKey = $currentDate->format('Y-m-d');
                    $formattedDate = $currentDate->format('D'); // Day abbreviation

                    $sale = $dailySales->firstWhere('date', $dateKey);
                    $salesData['labels'][] = $formattedDate;
                    $salesData['data'][] = $sale ? round($sale->total_sales, 2) : 0;

                    $currentDate->addDay();
                }
                break;

            case 'yearly':
                // Get data for the last 6 years
                $currentYear = $today->year;
                $startYear = $currentYear - 5; // Start from 5 years ago

                $yearlySales = Order::select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('SUM(total) as total_sales')
                )
                    ->whereYear('created_at', '>=', $startYear)
                    ->whereYear('created_at', '<=', $currentYear)
                    ->groupBy('year')
                    ->orderBy('year')
                    ->get();

                // Create array with all years in range
                for ($year = $startYear; $year <= $currentYear; $year++) {
                    $sale = $yearlySales->firstWhere('year', $year);

                    $salesData['labels'][] = (string) $year;
                    $salesData['data'][] = $sale ? round($sale->total_sales, 2) : 0;
                }
                break;

            default: // monthly
                // Last 6 months
                $startDate = $today->copy()->subMonths(5)->startOfMonth();
                $endDate = $today->copy()->endOfMonth();

                $monthlySales = Order::select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(total) as total_sales')
                )
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get();

                // Create array with all 6 months
                $currentDate = $startDate->copy();
                while ($currentDate <= $endDate) {
                    $year = $currentDate->format('Y');
                    $month = $currentDate->format('n');
                    $formattedMonth = $currentDate->format('M'); // Month abbreviation

                    $sale = $monthlySales->first(function ($item) use ($year, $month) {
                        return $item->year == $year && $item->month == $month;
                    });

                    $salesData['labels'][] = $formattedMonth;
                    $salesData['data'][] = $sale ? round($sale->total_sales, 2) : 0;

                    $currentDate->addMonth();
                }
                break;
        }

        return $salesData;
    }

    /**
     * Get best selling products
     *
     * @return array
     */
    private function getBestSellingProducts()
    {
        // First get the bestselling product IDs
        $bestSellingProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                'products.price',
                DB::raw('SUM(order_items.quantity) as total_sales'),
                DB::raw('SUM(order_items.total) as total_amount')
            )
            ->groupBy('products.id', 'products.name', 'products.price')
            ->orderByDesc('total_sales')
            ->limit(3)
            ->get();

        // Get the product IDs
        $productIds = $bestSellingProducts->pluck('id')->toArray();

        // Get the first image for each product
        $productImages = DB::table('product_images')
            ->whereIn('product_id', $productIds)
            ->select('product_id', DB::raw('MIN(id) as first_image_id'))
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        // Get the actual image paths
        $imageDetails = DB::table('product_images')
            ->whereIn('id', $productImages->pluck('first_image_id')->filter()->toArray())
            ->select('id', 'product_id', 'path')
            ->get()
            ->keyBy('product_id');

        return $bestSellingProducts->map(function ($product) use ($imageDetails) {
            $imagePath = null;
            if (isset($imageDetails[$product->id])) {
                $imagePath = env('APP_ASSET_URL') . '/storage/' . $imageDetails[$product->id]->path;
            }

            return [
                'product' => $product->name,
                'image' => $imagePath,
                'price' => 'LKR ' . number_format($product->price, 2),
                'sales' => $product->total_sales,
                'total' => 'LKR ' . number_format($product->total_amount, 2)
            ];
        });
    }

    /**
     * Get 6 most recent orders
     *
     * @return array
     */
    private function getRecentOrders()
    {
        $recentOrders = Order::with(['orderItems.product', 'user'])
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return $recentOrders->map(function ($order) {
            // Get the first product name from order items
            $productName = $order->orderItems->isNotEmpty()
                ? $order->orderItems->first()->product->name
                : 'Unknown Product';

            return [
                'id' => '#' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'product' => $productName,
                'date' => $order->created_at->format('M jS, Y'),
                'customer' => $order->first_name . ' ' . $order->last_name,
                'status' => ucfirst($order->status),
                'amount' => 'LKR ' . number_format($order->total, 2)
            ];
        });
    }

    /**
     * Get current user's orders filtered by status (pending/complete)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserOrdersByStatus(Request $request)
    {
        try {
            $userId = auth()->id();
            $statusFilter = $request->input('status', 'pending');

            // Validate status parameter
            if (!in_array($statusFilter, ['pending', 'complete'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid status. Must be either "pending" or "complete".'
                ], 400);
            }

            // Define status mapping
            $statusMapping = [
                'pending' => ['processing', 'shipped'],
                'complete' => ['delivered']
            ];

            $orderStatuses = $statusMapping[$statusFilter];

            // Get orders with order items and product details
            $orders = Order::with([
                'orderItems.product.images' => function ($query) {
                    $query->orderBy('id', 'asc')->limit(1); // Get first image only
                }
            ])
                ->where('user_id', $userId)
                ->whereIn('status', $orderStatuses)
                ->orderBy('created_at', 'desc')
                ->get();

            // Format the data for frontend
            $formattedData = [];

            foreach ($orders as $order) {
                foreach ($order->orderItems as $orderItem) {
                    $product = $orderItem->product;

                    // Get product image URL
                    $productImageUrl = null;
                    if ($product && $product->images->isNotEmpty()) {
                        $imagePath = $product->images->first()->path;
                        $productImageUrl = env('APP_ASSET_URL', config('app.url')) . '/storage/' . $imagePath;
                    }

                    // Calculate individual item price (after discount)
                    $originalPrice = $product ? $product->price : 0;
                    $discountPercentage = $product ? $product->discount : 0;
                    $discountAmount = ($originalPrice * $discountPercentage) / 100;
                    $itemPrice = $originalPrice - $discountAmount;

                    $formattedData[] = [
                        'order_id' => $order->id,
                        'order_number' => '#' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                        'status' => $order->status,
                        'created_at' => $order->created_at->format('M d, Y'),
                        'item' => [
                            'id' => $orderItem->id,
                            'product_id' => $orderItem->product_id,
                            'product_name' => $product ? $product->name : 'Unknown Product',
                            'product_image' => $productImageUrl,
                            'quantity' => $orderItem->quantity,
                            'price' => round($itemPrice, 0), // Individual item price after discount
                            'subtotal' => round($orderItem->total, 0), // Total for this item (quantity * discounted price)
                            'original_price' => round($originalPrice, 0),
                            'discount_percentage' => $discountPercentage
                        ]
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $formattedData,
                'filter' => $statusFilter,
                'total_items' => count($formattedData)
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching user orders by status', [
                'user_id' => auth()->id(),
                'status_filter' => $request->input('status'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function payhereNotify(Request $request)
    {
        $merchant_id = $request->input('merchant_id');
        $order_id = $request->input('order_id');
        $payhere_amount = $request->input('payhere_amount');
        $payhere_currency = $request->input('payhere_currency');
        $status_code = $request->input('status_code');
        $md5sig = $request->input('md5sig');

        $merchant_secret = config('payhere.merchant_secret');

        $local_md5sig = strtoupper(
            md5(
                $merchant_id .
                $order_id .
                $payhere_amount .
                $payhere_currency .
                $status_code .
                strtoupper(md5($merchant_secret))
            )
        );

        if (($local_md5sig === $md5sig) && ($status_code == 2)) {
            $order = Order::find($order_id);
            if ($order) {
                $order->status = 'processing';
                $order->save();

                // Deduct stock from product
                foreach ($order->orderItems as $item) {
                    $product = Product::find($item->product_id);
                    $product->stock = $product->stock - $item->quantity;
                    $product->save();
                }

                // Clear the user's cart
                Cart::where('user_id', $order->user_id)->delete();

                // Send order confirmation email
                $this->sendOrderConfirmationEmail($order);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    public function payhereReturn(Request $request)
    {
        // You can add logic here to display a success or failure message to the user.
        // For an API, you might redirect to a frontend URL with the order status.
        $order_id = $request->input('order_id');
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        return redirect($frontendUrl . '/order-success?order_id=' . $order_id);
    }
}
