<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category; // pastikan model Category ada

/*
|--------------------------------------------------------------------------
| API: Products
|--------------------------------------------------------------------------
| Endpoint ini digunakan untuk pencarian & filter produk dari Alpine.js
| Contoh: /api/products?q=beras
*/
Route::get('/products', function (Request $request) {
    $search = $request->query('q', '');
    $categoryId = $request->query('category_id', '');

    $products = Product::query()
        // Gabungkan dengan tabel product_usages untuk mendapatkan data popularitas
        ->leftJoin('product_usages', 'products.id', '=', 'product_usages.product_id')

        // Logika pencarian
        ->when($search, function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                // Tambahkan nama tabel untuk menghindari error kolom ambigu
                $sub->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.code', 'like', "%{$search}%");
            });
        })
        ->when($categoryId, fn($q) => $q->where('products.category_id', $categoryId))

        // Pilih kolom spesifik dari tabel products
        ->select('products.id', 'products.name', 'products.code', 'products.retail_price', 'products.stock', 'products.category_id', 'products.wholesale_price', 'products.wholesale_min_qty', 'products.cost_price')

        // Urutkan berdasarkan produk paling populer (usage_count), lalu berdasarkan nama
        ->orderBy('product_usages.usage_count', 'desc')
        ->orderBy('products.name', 'asc')

        ->limit(50)
        ->get();

    return response()->json($products);
});
/*
|--------------------------------------------------------------------------
| API: Categories
|--------------------------------------------------------------------------
| Digunakan untuk daftar kategori di Alpine.js filter atas
*/
Route::get('/categories', function () {
    $categories = Category::select('id', 'name')
        ->orderBy('name', 'asc')
        ->get();
    return response()->json($categories);
});
