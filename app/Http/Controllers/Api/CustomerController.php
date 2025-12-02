<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
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
    }
}
