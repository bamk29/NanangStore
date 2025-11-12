<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category; // pastikan model Category ada
use App\Models\Customer; // Tambahkan model Customer

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

    $query = Product::query()
        // Gabungkan dengan tabel product_usages untuk mendapatkan data popularitas
        ->leftJoin('product_usages', 'products.id', '=', 'product_usages.product_id')
        // Pilih kolom spesifik dari tabel products
        ->select('products.id', 'products.name', 'products.code', 'products.retail_price', 'products.stock', 'products.category_id', 'products.wholesale_price', 'products.wholesale_min_qty', 'products.cost_price', 'products.description');

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('products.name', 'like', "%{$search}%")
              ->orWhere('products.code', 'like', "%{$search}%")
              ->orWhere('products.description', 'like', "%{$search}%");
        });
    } else {
        // Logika pengurutan: populer jika tidak ada pencarian
        $query->orderBy('product_usages.usage_count', 'desc')->orderBy('products.name', 'asc');
    }

    $products = $query
        ->when($categoryId, fn($q) => $q->where('products.category_id', $categoryId))
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

/*
|--------------------------------------------------------------------------
| API: Customers
|--------------------------------------------------------------------------
| Endpoint ini digunakan untuk pencarian customer dari Alpine.js di kasir
| Contoh: /api/customers?q=nama
*/
Route::get('/customers', function (Request $request) {
    $search = $request->query('q', '');

    $query = Customer::query();

    if (empty($search)) {
        // Jika tidak ada pencarian, kembalikan 10 pelanggan terbaru
        $customers = $query->latest()->limit(10)->get(['id', 'name', 'phone', 'debt']);
    } else {
        // Jika ada pencarian, lakukan pencarian
        $customers = $query->where('name', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%")
            ->limit(10)
            ->get(['id', 'name', 'phone', 'debt']);
    }

    return response()->json($customers);
});

/*
|--------------------------------------------------------------------------
| API: Find Product by Code
|--------------------------------------------------------------------------
| Digunakan oleh barcode scanner untuk mendapatkan produk via kodenya.
*/
Route::get('/products/by-code/{code}', function ($code) {
    $product = Product::where('code', $code)->first();

    if ($product) {
        return response()->json($product);
    }

    return response()->json(['message' => 'Produk tidak ditemukan'], 404);
});

/*
|--------------------------------------------------------------------------
| API: Products for Purchase Order
|--------------------------------------------------------------------------
| Endpoint ini khusus untuk form Purchase Order, menyertakan data harga modal.
*/
Route::get('/purchase-order-products', function (Request $request) {
    $search = $request->query('q', '');

    $products = \App\Models\Product::query()
        ->when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        })
        ->select('id', 'name', 'code', 'stock', 'units_in_box', 'unit_cost', 'box_cost')
        ->limit(10)
        ->get();

    return response()->json($products);
});
