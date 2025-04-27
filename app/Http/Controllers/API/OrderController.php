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
            foreach ($cartItems as $item) {
                $total += ($item->product->price - $item->product->discount) * $item->quantity;
            }

            // Apply discount and tax
            $total = $total - $request->discount + $request->tax + $request->shipping_rate;

            // Create the order
            $order = Order::create([
                'user_id' => $userId,
                'shipping_address' => $request->shipping_address,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'total' => $total,
                'discount' => $request->discount,
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
            'status' => 'sometimes|string|in:pending,processing,shipped,completed,cancelled',
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
}