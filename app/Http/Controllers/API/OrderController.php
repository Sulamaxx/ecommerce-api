<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
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
                $total += ($item->product->price - $item->product->discount) * $item->quantity;
                $totalItemDiscount += $item->product->discount * $item->quantity;
            }

            // Apply discount and tax
            $total = $total - $request->discount + $request->tax + $request->shipping_rate;

            // Create the order
            $order = Order::create([
                'user_id' => $userId,
                'shipping_address' => $request->shipping_address,
                'payment_method' => $request->payment_method,
                'status' => 'processing',
                'total' => $total,
                'discount' => $request->discount + $totalItemDiscount,
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
                    'total' => $item->product->price * $item->quantity,
                    'discount' => 0, // Apply individual item discounts if needed
                ]);

                // Deduct stock from product
                $product = Product::find($item->product_id);
                $product->stock = $product->stock - $item->quantity;
                $product->save();
            }

            // Clear the user's cart
            Cart::where('user_id', $userId)->delete();

            DB::commit();

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
        if (auth()->id() !== $order->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate request data
        $validator = Validator::make($request->all(), [
            'payment_method' => 'sometimes|string',
            'status' => 'sometimes|string|in:processing,shipped,delivered,cancelled',
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

            // Handle cancellation - restore product stock if order is being cancelled
            if ($request->has('status') && $request->status == 'cancelled' && $order->status != 'cancelled') {
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
                $query->where(function($q) use ($searchTerm) {
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
                        'currentPage' => (int)$page,
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
                        'currentPage' => (int)$page,
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
                $query->where(function($q) use ($searchTerm) {
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
                    'id' => (string)$order->id,
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
                    'products' => $order->orderItems->map(function($item) use ($order) {
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
                        'currentPage' => (int)$page,
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
}