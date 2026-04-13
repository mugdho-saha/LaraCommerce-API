<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\StoreProductRequest;
use App\Http\Requests\api\v1\UpdateProductRequest;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\Api\V1\ProductResource;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::where('product_status', true)
        ->with('category')
        ->get();

        // Use ::collection() for multiple items
        return ProductResource::collection($products)
        ->additional([
            'success' => true,
            'message' => 'Products retrieved successfully.'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
{
    $validated = $request->validated();

    // 1. Handle the Image Upload
    if ($request->hasFile('product_image')) {
        // This saves the file to storage/app/public/products 
        // and returns the path (e.g., "products/filename.jpg")
        $path = $request->file('product_image')->store('products', 'public');
        
        // 2. Replace the file object in the array with the string path
        $validated['product_image'] = $path;
    }

    // 3. Create the product with the path string
    $product = Product::create($validated);

    return (new ProductResource($product))
    ->additional([
        'success' => true,
        'message' => 'Product added successfully.'
    ], 201);
}

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // Eager load the category so the Resource can access the name
        $product->load('category');

        return (new ProductResource($product))
            ->additional([
                'success' => true,
                'message' => 'Product details retrieved successfully.'
            ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
{
    // Get only the validated data
    $data = $request->validated();

    if ($request->hasFile('product_image')) {
        // A new file is being uploaded
        if ($product->product_image) {
            Storage::disk('public')->delete($product->product_image);
        }
        $data['product_image'] = $request->file('product_image')->store('products', 'public');
    } else {
        // CRITICAL: Remove the key entirely so the database column is NOT updated
        unset($data['product_image']);
    }

    // This now only updates the fields present in $data
    $product->update($data);

    // Refresh the model instance to ensure we have the latest DB state
    $product->refresh();

    return new ProductResource($product);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // This removes the record from the database
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.'
        ], 200);
    }
}
