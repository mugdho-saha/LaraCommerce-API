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
     * Get all products with pagination
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        
        $products = Product::where('product_status', true)
            ->with('category')
            ->latest()
            ->paginate($perPage);

        return ProductResource::collection($products)
            ->additional([
                'success' => true,
                'message' => 'Products retrieved successfully.'
            ]);
    }

    /**
     * Get single product
     */
    public function show(Product $product)
    {
        if (!$product->product_status) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        }

        $product->load('category');

        return (new ProductResource($product))
            ->additional([
                'success' => true,
                'message' => 'Product retrieved successfully.'
            ]);
    }

    /**
     * Get products by category
     */
    public function byCategory(Request $request, $category)
    {
        $perPage = $request->get('per_page', 15);
        
        // Support both category ID and slug
        $categoryModel = is_numeric($category) 
            ? Category::find($category)
            : Category::where('slug', $category)->first();

        if (!$categoryModel) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }

        $products = Product::where('product_status', true)
            ->where('category_id', $categoryModel->id)
            ->with('category')
            ->latest()
            ->paginate($perPage);

        return ProductResource::collection($products)
            ->additional([
                'success' => true,
                'message' => "Products in {$categoryModel->name} retrieved successfully.",
                'category' => $categoryModel
            ]);
    }

    /**
     * Search products
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        $query = $request->get('q');
        $perPage = $request->get('per_page', 15);

        $products = Product::where('product_status', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('sku', 'LIKE', "%{$query}%");
            })
            ->with('category')
            ->latest()
            ->paginate($perPage);

        return ProductResource::collection($products)
            ->additional([
                'success' => true,
                'message' => 'Search results retrieved successfully.',
                'search_query' => $query
            ]);
    }

    /**
     * Advanced filter products
     */
    public function filter(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'sort_by' => 'nullable|in:price_asc,price_desc,name_asc,name_desc,latest,oldest',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        $query = Product::where('product_status', true)->with('category');

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by stock availability
        if ($request->has('in_stock') && $request->in_stock == true) {
            $query->where('stock', '>', 0);
        }

        // Sorting
        switch ($request->get('sort_by', 'latest')) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'oldest':
                $query->oldest();
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products)
            ->additional([
                'success' => true,
                'message' => 'Filtered products retrieved successfully.',
                'filters' => $request->only(['category_id', 'min_price', 'max_price', 'sort_by'])
            ]);
    }

    /**
     * Get featured products
     */
    public function featured(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        
        $products = Product::where('product_status', true)
            ->where('is_featured', true) // Assuming you have this column
            ->with('category')
            ->latest()
            ->paginate($perPage);

        return ProductResource::collection($products)
            ->additional([
                'success' => true,
                'message' => 'Featured products retrieved successfully.'
            ]);
    }

    /**
     * Get latest products
     */
    public function latest(Request $request)
    {
        $limit = $request->get('limit', 10);
        
        $products = Product::where('product_status', true)
            ->with('category')
            ->latest()
            ->limit($limit)
            ->get();

        return ProductResource::collection($products)
            ->additional([
                'success' => true,
                'message' => 'Latest products retrieved successfully.'
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
