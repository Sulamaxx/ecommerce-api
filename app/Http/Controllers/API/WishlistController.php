<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    // Get all wishlist items for the authenticated user
    public function index(Request $request)
    {

    // Clean up inactive products from wishlist
    $inactiveProductIds = Product::where('status', 'INACTIVE')->pluck('id');
    if ($inactiveProductIds->count() > 0) {
        Wishlist::where('user_id', $request->user()->id)
            ->whereIn('product_id', $inactiveProductIds)
            ->delete();
    }

        $wishlist = Wishlist::with([
            'product' => function($query) {
            $query->where('status', 'ACTIVE');
            },
            'product.images' => function($query) {
                $query->orderBy('id', 'asc')->limit(1); // Get first image only
            }
        ])
        ->where('user_id', $request->user()->id)
        ->get()
        ->filter(function($item) {
            return $item->product !== null; // Remove items with null product
        });

        // Format the response with product details and first image
        $formattedWishlist = $wishlist->map(function($item) {
            $product = $item->product;
            
            // Get product image URL
            $productImageUrl = null;
            if ($product && $product->images->isNotEmpty()) {
                $imagePath = $product->images->first()->path;
                $productImageUrl = env('APP_ASSET_URL', config('app.url')) . '/storage/' . $imagePath;
            }
            
            // Calculate discounted price based on discount type - UPDATED
            $originalPrice = $product ? $product->price : 0;
            $discount = $product ? $product->discount : 0;
            $discountType = $product ? $product->discount_type : 'percentage';
            $currency = $product ? $product->currency : 'LKR';
            
            $discountedPrice = $this->calculateDiscountedPrice($originalPrice, $discountType, $discount);
            
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
                    'currency' => $currency,
                    'discount_type' => $discountType,
                    'discount' => $product->discount > 0 ? (float) $product->discount : null,
                    'discounted_price' => round($discountedPrice, 0),
                    'image' => $productImageUrl,
                    'rating' => $product->rating,
                    'stock' => $product->stock,
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

        // Check if product is active
        $product = Product::where('id', $request->product_id)
            ->where('status', 'ACTIVE')
            ->first();
        
        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not available'
            ], 400);
        }

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

            // Calculate discounted price based on discount type - UPDATED
            $originalPrice = $product ? $product->price : 0;
            $discount = $product ? $product->discount : 0;
            $discountType = $product ? $product->discount_type : 'percentage';
            $currency = $product ? $product->currency : 'LKR';
            
            $discountedPrice = $this->calculateDiscountedPrice($originalPrice, $discountType, $discount);

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
                    'currency' => $currency,
                    'discount_type' => $discountType,
                    'discount' => $product->discount > 0 ? (float) $product->discount : null,
                    'discounted_price' => round($discountedPrice, 0),
                    'image' => $productImageUrl,
                    'rating' => $product->rating,
                    'stock' => $product->stock,
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

    /**
     * Calculate discounted price based on discount type
     * 
     * @param float $price Original price
     * @param string $discountType 'percentage' or 'amount'
     * @param float $discount Discount value
     * @return float Final price after discount
     */
    protected function calculateDiscountedPrice($price, $discountType, $discount)
    {
        if (!$discount || $discount <= 0) {
            return $price;
        }
        
        if ($discountType === 'amount') {
            return max(0, $price - $discount);
        } else {
            // Default to percentage
            return $price - ($price * $discount / 100);
        }
    }
}