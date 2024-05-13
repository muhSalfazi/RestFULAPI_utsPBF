<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['prefix' => 'categories'], function () {
    Route::get('/', [CategoryController::class, 'index']); // Endpoint untuk melihat semua kategori
    Route::post('/', [CategoryController::class, 'store']); // Endpoint untuk membuat kategori baru
    Route::put('/{category}', [CategoryController::class, 'update']); // Endpoint untuk menyunting kategori
    Route::delete('/{category}', [CategoryController::class, 'destroy']); // Endpoint untuk menghapus kategori
});


Route::group(['prefix' => 'api'], function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});