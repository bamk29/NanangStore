<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('q', '');
        $categoryId = $request->query('category_id', '');

        // Generate a unique cache key based on query parameters
        $cacheKey = 'products_' . md5($search . '_' . $categoryId);

        // Define cache duration: 30 minutes for default list (popular), 
        // but we won't cache search results to ensure real-time accuracy for specific queries.
        // Actually, let's only cache the "default popular list" (no search, no category)
        if (empty($search) && empty($categoryId)) {
            return Cache::remember('products_popular_default', 1800, function () {
                return $this->fetchProducts('', '');
            });
        }

        // For search results or filtered lists, we fetch directly (or could cache with shorter TTL if needed)
        return $this->fetchProducts($search, $categoryId);
    }

    private function fetchProducts($search, $categoryId)
    {
        $query = Product::query()
            ->leftJoin('product_usages', 'products.id', '=', 'product_usages.product_id')
            ->select('products.id', 'products.name', 'products.code', 'products.retail_price', 'products.stock', 'products.category_id', 'products.wholesale_price', 'products.wholesale_min_qty', 'products.cost_price', 'products.description');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('products.code', 'like', "%{$search}%")
                  ->orWhere('products.description', 'like', "%{$search}%");
            });
        } else {
            $query->orderBy('product_usages.usage_count', 'desc')->orderBy('products.name', 'asc');
        }

        return response()->json($query
            ->when($categoryId, fn($q) => $q->where('products.category_id', $categoryId))
            ->limit(50)
            ->get());
    }

    public function getByCode($code)
    {
        $product = Product::where('code', $code)->first();

        if ($product) {
            return response()->json($product);
        }

        return response()->json(['message' => 'Produk tidak ditemukan'], 404);
    }

    public function getCategories()
    {
        // Cache categories for 24 hours (86400 seconds)
        $categories = Cache::remember('product_categories', 86400, function () {
            return Category::select('id', 'name')
                ->orderBy('name', 'asc')
                ->get();
        });
        
        return response()->json($categories);
    }

    public function getForPurchaseOrder(Request $request)
    {
        $search = $request->query('q', '');

        $products = Product::query()
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            })
            ->select('id', 'name', 'code', 'stock', 'units_in_box', 'unit_cost', 'box_cost', 'cost_price')
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function getRecommendations(Customer $customer)
    {
        $products = TransactionDetail::whereHas('transaction', function ($query) use ($customer) {
                $query->where('customer_id', $customer->id);
            })
            ->select('product_id', DB::raw('count(*) as frequency'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('frequency')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return $item->product;
            })
            ->filter(); // Filter out nulls if product was deleted

        return response()->json($products->values());
    }

    public function search(Request $request)
    {
        $search = $request->query('q', '');
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $products = Product::where('name', 'like', "%{$search}%")
            ->orWhere('code', 'like', "%{$search}%")
            ->orWhere('barcode', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->select('id', 'name', 'code', 'barcode', 'stock')
            ->limit(10)
            ->get();

        return response()->json($products);
    }
}
