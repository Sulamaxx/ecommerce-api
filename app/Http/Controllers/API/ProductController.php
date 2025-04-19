<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\ProductImage;

class ProductController extends Controller
{
    /**
     * Store a newly created product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:255',
            'brandName' => 'nullable|string|max:255',
            'sku' => 'required|string|max:255|unique:products,name',
            'stockQuantity' => 'required|integer|min:0',
            'price' => 'required|integer|min:0',
            'discountPercentage' => 'required|integer|min:0',
            'images' => 'required|array|min:1|max:3',
            'images.*' => 'required|image|mimes:jpeg,png|max:2048',
            'userGuide' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Start a database transaction
            DB::beginTransaction();

            // Calculate the discount amount based on percentage
            $price = $request->price;
            $discountPercentage = $request->discountPercentage;
            $discount = ($price * $discountPercentage) / 100;

            // Create the product
            $product = new Product();
            $product->name = $request->name;
            $product->description = $request->description;
            $product->category = $request->category;
            $product->price = $price;
            $product->discount = $discount;
            $product->stock = $request->stockQuantity;

            // Handle the user guide PDF if uploaded
            if ($request->hasFile('userGuide')) {
                $pdfPath = $request->file('userGuide')->store('product_guides', 'public');
                $product->user_guide_pdf = $pdfPath;
            }

            $product->save();

            // Handle product images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    $imagePath = $imageFile->store('product_images', 'public');
                    
                    // Create the product image record
                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->path = $imagePath;
                    $productImage->save();
                }
            }

            // Commit the transaction
            DB::commit();

            // Return the created product with its images
            return response()->json([
                'status' => 'success',
                'message' => 'Product created successfully',
                'data' => [
                    'product' => $product,
                    'images' => $product->images
                ]
            ], 201);

        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Find the product
        $product = Product::findOrFail($id);

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:255',
            'brandName' => 'nullable|string|max:255',
            'sku' => 'required|string|max:255|unique:products,name,'.$id,
            'stockQuantity' => 'required|integer|min:0',
            'price' => 'required|integer|min:0',
            'discountPercentage' => 'required|integer|min:0',
            'images' => 'nullable|array|max:3',
            'images.*' => 'nullable|image|mimes:jpeg,png|max:2048',
            'userGuide' => 'nullable|file|mimes:pdf|max:5120',
            'removeImages' => 'nullable|array',
            'removeImages.*' => 'nullable|integer|exists:product_images,id',
            'removeUserGuide' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Start a database transaction
            DB::beginTransaction();

            // Calculate the discount amount based on percentage
            $price = $request->price;
            $discountPercentage = $request->discountPercentage;
            $discount = ($price * $discountPercentage) / 100;

            // Update product details
            $product->name = $request->name;
            $product->description = $request->description;
            $product->category = $request->category;
            $product->price = $price;
            $product->discount = $discount;
            $product->stock = $request->stockQuantity;

            // Remove user guide if requested
            if ($request->removeUserGuide && $product->user_guide_pdf) {
                Storage::disk('public')->delete($product->user_guide_pdf);
                $product->user_guide_pdf = null;
            }

            // Handle the user guide PDF if a new one is uploaded
            if ($request->hasFile('userGuide')) {
                // Delete the old file if it exists
                if ($product->user_guide_pdf) {
                    Storage::disk('public')->delete($product->user_guide_pdf);
                }
                
                // Store the new file
                $pdfPath = $request->file('userGuide')->store('product_guides', 'public');
                $product->user_guide_pdf = $pdfPath;
            }

            $product->save();

            // Remove images if requested
            if ($request->has('removeImages') && is_array($request->removeImages)) {
                foreach ($request->removeImages as $imageId) {
                    $image = ProductImage::find($imageId);
                    if ($image && $image->product_id == $product->id) {
                        // Delete the file
                        Storage::disk('public')->delete($image->path);
                        
                        // Delete the record
                        $image->delete();
                    }
                }
            }

            // Add new images if uploaded
            if ($request->hasFile('images')) {
                // Check if adding these would exceed the 3 image limit
                $currentImageCount = $product->images()->count();
                $newImageCount = count($request->file('images'));
                
                if ($currentImageCount + $newImageCount > 3) {
                    throw new \Exception('Maximum 3 images allowed per product.');
                }
                
                foreach ($request->file('images') as $imageFile) {
                    $imagePath = $imageFile->store('product_images', 'public');
                    
                    // Create the product image record
                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->path = $imagePath;
                    $productImage->save();
                }
            }

            // Commit the transaction
            DB::commit();

            // Return the updated product with its images
            return response()->json([
                'status' => 'success',
                'message' => 'Product updated successfully',
                'data' => [
                    'product' => $product,
                    'images' => $product->images
                ]
            ], 200);

        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Start a database transaction
            DB::beginTransaction();
            
            // Delete the product images from storage
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
            }
            
            // Delete the user guide PDF if it exists
            if ($product->user_guide_pdf) {
                Storage::disk('public')->delete($product->user_guide_pdf);
            }
            
            // Delete the product (this will cascade delete the images due to foreign key constraint)
            $product->delete();
            
            // Commit the transaction
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Product deleted successfully'
            ], 200);
            
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all products.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::with('images')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $products
        ], 200);
    }

    /**
     * Get a specific product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::with('images')->findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'data' => $product
        ], 200);
    }
}