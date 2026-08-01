<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::orderBy('name')->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:categories,name',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => $category,
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'data' => $category,
        ]);
    }

    public function update(
        Request $request,
        Category $category
    ): JsonResponse {
        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')
                    ->ignore($category->id),
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => $category,
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->vehicles()->exists()) {
            return response()->json([
                'message' => 'Category cannot be deleted because it contains vehicles.',
            ], 409);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }
}