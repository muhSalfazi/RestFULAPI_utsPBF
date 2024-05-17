<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->get();
        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::with('category')->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product Tidak Tersedia'], 404);
        }
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|integer',
            'image' => 'required|file|image',  // Validasi file gambar
            'category_id' => 'required|string',  // Menggunakan nama kategori
            'expired_at' => 'required|date',
            'modified_by' => 'sometimes|string|max:255'  // Optional
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $category = Category::where('name', $request->category_id)->first();
        if (!$category) {
            return response()->json(['message' => 'Category Tidak Tersedia'], 404);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/images');
            $imagePath = Storage::url($path);
        }

        $product = new Product($request->except('image'));
        $product->category_id = $category->id;
        $product->image = $imagePath;  // Menyimpan jalur gambar
        $product->save();

        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        // Get authenticated user
        $user = Auth::user();

        // Retrieve the product
        $product = Product::find($id);

        // If product not found, return error response
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Define validation rules for all fields
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|integer',
            'image' => 'sometimes|file|image',  
            'category_id' => 'sometimes|string|max:255', 
            'expired_at' => 'sometimes|date',
            'modified_by' => 'sometimes|string|max:255'  
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors())->setStatusCode(422);
        }

        // If request has 'category_id', update the product's category_id
        if ($request->has('category_id')) {
            // Retrieve the category based on name
            $category = Category::where('name', $request->category_id)->first();
            if (!$category) {
                return response()->json(['message' => 'Category tidak tersedia di dalam database'], 404);
            }
            $product->category_id = $category->id;
        }

        // If request has 'name', update the product's name
        if ($request->has('name')) {
            $product->name = $request->name;
        }

        // If request has 'description', update the product's description
        if ($request->has('description')) {
            $product->description = $request->description;
        }

        // If request has 'price', update the product's price
        if ($request->has('price')) {
            $product->price = $request->price;
        }

        // If request has 'image', update the product's image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/images');
            $product->image = Storage::url($path);
        }

        // If request has 'expired_at', update the product's expired_at
        if ($request->has('expired_at')) {
            $product->expired_at = $request->expired_at;
        }

        // Ensure user is authenticated before proceeding
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Retrieve user email
        $userEmail = $user->email;

        // Add user email as modified_by
        $product->modified_by = $userEmail;

        // Save the updated product
        $product->save();

        return response()->json([
            'msg' => 'Data dengan id: ' . $id . ' berhasil diupdate',
            'data' => $product
        ], 200);
    }





    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product Tidak Tersedia'], 404);
        }

        // Hapus gambar terkait
        if ($product->image) {
            Storage::delete(str_replace('/storage', 'public', $product->image));
        }

        $product->delete();
        return response()->json(['message' => 'Product Berhasil DI hapus']);
    }
}