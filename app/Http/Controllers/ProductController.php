<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
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
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product Tidak Tersedia'], 404);
        }

        // Validasi hanya akan diterapkan untuk atribut-atribut yang ada dalam permintaan
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|integer',
            'category_id' => 'sometimes|string',
            'expired_at' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Update updated_at menjadi waktu saat ini menggunakan Carbon
        $product->updated_at = Carbon::now();

        // Update atribut-atribut yang diberikan dalam permintaan
        if ($request->has('name')) {
            $product->name = $request->input('name');
        }
        if ($request->has('description')) {
            $product->description = $request->input('description');
        }
        if ($request->has('price')) {
            $product->price = $request->input('price');
        }
        if ($request->has('category_id')) {
            $category = Category::where('name', $request->input('category_id'))->first();
            if (!$category) {
                return response()->json(['message' => 'Category Tidak Tersedia'], 404);
            }
            $product->category_id = $category->id;
        }
        if ($request->has('expired_at')) {
            $product->expired_at = $request->input('expired_at');
        }

        // Mendapatkan email pengguna terautentikasi
        $userEmail = Auth::user()->email;

        // Memperbarui modified_by dengan email pengguna yang terautentikasi
        $product->modified_by = $userEmail;

        // Jika gambar baru diunggah, simpan jalur gambar yang baru
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/images');
            $imagePath = Storage::url($path);
            $product->image = $imagePath;
        }

        // Simpan pembaruan ke dalam basis data
        $product->save();

        return response()->json($product);
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