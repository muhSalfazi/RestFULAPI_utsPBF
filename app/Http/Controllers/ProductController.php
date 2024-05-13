<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->get();
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $product = Product::create($request->validated());

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products');
            $product->image = $path;
            $product->save();
        }

        return response()->json(['message' => 'Produk berhasil dibuat'], 201);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($request->validated());

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::delete($product->image);
            }
            $path = $request->file('image')->store('products');
            $product->image = $path;
            $product->save();
        }

        return response()->json(['message' => 'Produk berhasil diperbarui']);
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Produk berhasil dihapus']);
    }
}