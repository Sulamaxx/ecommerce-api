<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * Add a product to cart
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::find($request->product_id);
        
        // Check if product has enough stock
        if ($product->stock < $request->quantity) {
            return response()->json([
                'status' => false,
                'message' => 'Not enough stock available',
                'available_stock' => $product->stock
            ], 400);
        }

        // Check if product already exists in cart
        $cartItem = Cart::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $request->quantity;
            
            // Check if total quantity exceeds available stock
            if ($newQuantity > $product->stock) {
                return response()->json([
                    'status' => false,
                    'message' => 'Adding this quantity would exceed available stock',
                    'available_stock' => $product->stock,
                    'current_cart_quantity' => $cartItem->quantity
                ], 400);
            }
            
            $cartItem->quantity = $newQuantity;
            $cartItem->save();
        } else {
            $cartItem = Cart::create([
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Product added to cart successfully',
            'data' => $cartItem
        ], 200);
    }

    /**
     * Get all cart items for a user
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCartItems()
    {
        $cartItems = Cart::where('user_id', auth()->id())
            ->with(['product' => function($query) {
                $query->select('id', 'name', 'description', 'price', 'discount', 'stock', 'category');
            }])
            ->get();

        $formattedCartItems = $cartItems->map(function($item) {
            // Get the first image for each product
            $productImage = $item->product->images()->first();
            $imagePath = $productImage ?  env('APP_ASSET_URL') . '/storage/' . $productImage->path : null;
            
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'description' => $item->product->description,
                    'price' => $item->product->price,
                    'discount' => $item->product->discount,
                    'stock' => $item->product->stock,
                    'category' => $item->product->category,
                    'image' => $imagePath
                ],
                'total_price' => $item->quantity * ($item->product->price - ($item->product->price * $item->product->discount)/100),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Cart items retrieved successfully',
            'data' => $formattedCartItems,
            'total_items' => $cartItems->sum('quantity'),
            'total_amount' => $formattedCartItems->sum('total_price')
        ], 200);
    }

    /**
     * Update cart item quantity
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCartItem(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $cartItem = Cart::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$cartItem) {
            return response()->json([
                'status' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        $product = Product::find($cartItem->product_id);
        
        // Check if requested quantity is available in stock
        if ($request->quantity > $product->stock) {
            return response()->json([
                'status' => false,
                'message' => 'Requested quantity exceeds available stock',
                'available_stock' => $product->stock
            ], 400);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'status' => true,
            'message' => 'Cart item updated successfully',
            'data' => $cartItem
        ], 200);
    }

    /**
     * Delete cart item
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCartItem($id)
    {
        $cartItem = Cart::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$cartItem) {
            return response()->json([
                'status' => false,
                'message' => 'Cart item not found',
                'user_id'=> auth()->id(),
                'id'=> $id
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'status' => true,
            'message' => 'Cart item deleted successfully',

        ], 200);
    }

    /**
     * Clear all items from cart
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCart()
    {
        Cart::where('user_id', auth()->id())->delete();

        return response()->json([
            'status' => true,
            'message' => 'Cart cleared successfully'
        ], 200);
    }
}