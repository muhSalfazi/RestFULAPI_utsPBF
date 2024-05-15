<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:categories|max:255',
        ]);

        // Jika validasi gagal, kembalikan pesan error
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Buat kategori baru
        $category = Category::create([
            'name' => $request->name,
        ]);

        // Kembalikan respons sukses
        return response()->json([
            'message' => 'Category Berhasil Di buat',
            'category' => $category
        ], 201);
    }

    public function update(Request $request, $id)
    {
        // Cari kategori berdasarkan ID
        $category = Category::find($id);

        // Jika kategori tidak ditemukan, kembalikan pesan error
        if (!$category) {
            return response()->json(['message' => 'Category Tidak Tersedia'], 404);
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
        ]);

        // Jika validasi gagal, kembalikan pesan error
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Update nama kategori
        $category->update($request->only('name'));

        // Kembalikan respons sukses
        return response()->json([
            'message' => 'Category Berhasil Diupdate',
        ], 200);
    }

    public function destroy($id)
    {
        // Cari kategori berdasarkan ID
        $category = Category::find($id);

        // Jika kategori tidak ditemukan, kembalikan pesan error
        if (!$category) {
            return response()->json(['message' => 'Category Tidak Tersedia'], 404);
        }

        // Hapus kategori
        $category->delete();

        // Kembalikan respons sukses
        return response()->json([
            'message' => 'Category Berhasil Dihapus'
        ], 200);
    }

    public function show($id)
    {
        // Cari kategori berdasarkan ID
        $category = Category::find($id);

        // Jika kategori tidak ditemukan, kembalikan pesan error
        if (!$category) {
            return response()->json(['message' => 'Category TIdak Tersedia'], 404);
        }

        // Kembalikan data kategori
        return response()->json($category);
    }
}