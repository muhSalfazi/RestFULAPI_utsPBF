<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        Category::create($request->validated());
        return response()->json(['message' => 'Kategori berhasil dibuat']);
    }

    public function update(Request $request, Category $category)
    {
        $category->update($request->validated());
        return response()->json(['message' => 'Kategori berhasil diubah']);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Kategori berhasil dihapus']);
    }
}