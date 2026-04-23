<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\category\create\CreateCategory;
use App\Http\Resources\Category\CategoryResource;
use Illuminate\Http\Request;

class CategoryController extends Controller {
    public function index() {
        return $this->sendJsonWithData(Category::all(), CategoryResource::class);
    }

    public function store(CreateCategory $request) {
        $category = Category::create([
            'id' => $request->get('id'),
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'Category' => CategoryResource::make($category)
        ], 201);
    }

    public function show(Category $category) {
        return response()->json($category);
    }

    public function update(Request $request, Category $category) {
        $request->validate(['name' => 'required|unique:categories,name,'. $category->id]);
        $category->update($request->all());
        return response()->json($category);
    }

    public function destroy(Category $category) {
        $category->delete();
        return response()->json(null, 204);
    }
}
