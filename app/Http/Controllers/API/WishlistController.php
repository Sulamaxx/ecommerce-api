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
        $wishlist = Wishlist::with('product')
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json($wishlist);
    }

    // Add a product to the wishlist
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        return response()->json($wishlist, 201);
    }

    // Remove a product from the wishlist
    public function destroy(Request $request, $product_id)
    {
        $deleted = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $product_id)
            ->delete();

        return response()->json(['deleted' => $deleted > 0]);
    }
}
