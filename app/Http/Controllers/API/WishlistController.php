<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    // Get all wishlist items for the authenticated user
    public function index(Request $request)
    {
        $wishlist = Wishlist::with([
            'product',
            'product.images' => function($query) {
                $query->orderBy('id', 'asc')->limit(1); // Get first image only
            }
        ])
        ->where('user_id', $request->user()->id)
        ->get();

        // Format the response with product details and first image
        $formattedWishlist = $wishlist->map(function($item) {
            $product = $item->product;
            
            // Get product image URL
            $productImageUrl = null;
            if ($product && $product->images->isNotEmpty()) {
                $imagePath = $product->images->first()->path;
                $productImageUrl = env('APP_ASSET_URL', config('app.url')) . '/storage/' . $imagePath;
            }
            
            // Calculate discounted price
            $originalPrice = $product ? $product->price : 0;
            $discountPercentage = $product ? $product->discount : 0;
            $discountAmount = ($originalPrice * $discountPercentage) / 100;
            $discountedPrice = $originalPrice - $discountAmount;
            
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'product_id' => $item->product_id,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'category' => $product->category,
                    'price' => $originalPrice,
                    'discount' => $product->discount > 0 ? (float) $product->discount : null,
                    'discounted_price' => round($discountedPrice, 0),
                    'image' => $productImageUrl,
                    'rating' => $product->rating,
                    'is_in_stock' => $product->stock > 0
                ]
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedWishlist,
            'count' => $formattedWishlist->count()
        ]);
    }

    // Add a product to the wishlist
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        try {
            $wishlist = Wishlist::firstOrCreate([
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id,
            ]);

            // Load the product with its first image for the response
            $wishlist->load([
                'product',
                'product.images' => function($query) {
                    $query->orderBy('id', 'asc')->limit(1);
                }
            ]);

            // Format the response similar to index method
            $product = $wishlist->product;
            $productImageUrl = null;
            if ($product && $product->images->isNotEmpty()) {
                $imagePath = $product->images->first()->path;
                $productImageUrl = env('APP_ASSET_URL', config('app.url')) . '/storage/' . $imagePath;
            }

            $originalPrice = $product ? $product->price : 0;
            $discountPercentage = $product ? $product->discount : 0;
            $discountAmount = ($originalPrice * $discountPercentage) / 100;
            $discountedPrice = $originalPrice - $discountAmount;

            $formattedWishlist = [
                'id' => $wishlist->id,
                'user_id' => $wishlist->user_id,
                'product_id' => $wishlist->product_id,
                'created_at' => $wishlist->created_at,
                'updated_at' => $wishlist->updated_at,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'category' => $product->category,
                    'price' => $originalPrice,
                    'discount' => $product->discount > 0 ? (float) $product->discount : null,
                    'discounted_price' => round($discountedPrice, 0),
                    'image' => $productImageUrl,
                    'is_in_stock' => $product->stock > 0
                ]
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Product added to wishlist successfully',
                'data' => $formattedWishlist
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add product to wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Remove a product from the wishlist
    public function destroy(Request $request, $product_id)
    {
        try {
            $deleted = Wishlist::where('user_id', $request->user()->id)
                ->where('product_id', $product_id)
                ->delete();

            return response()->json([
                'status' => 'success',
                'message' => $deleted > 0 ? 'Product removed from wishlist successfully' : 'Product not found in wishlist',
                'deleted' => $deleted > 0
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove product from wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}