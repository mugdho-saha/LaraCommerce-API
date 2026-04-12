<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\StoreCategoryRequest;
use App\Http\Requests\api\v1\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully.',
            'data'    => $categories
        ], 200);
    }

    public function store(StoreCategoryRequest $request){
        $validated = $request->validated();
        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => $category
        ], 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        // $category is already the correct record found via the slug
        $category->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data' => $category
        ], 200);
    }

    public function destroy(Category $category)
    {
        // This removes the record from the database
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.'
        ], 200);
    }
}
