<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\AuthController;

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

Route::post('/login', [AuthController::class, 'login']);

// Products
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('/products/by-code/{code}', [ProductController::class, 'getByCode']);
    Route::get('/products/recommendations/{customer}', [ProductController::class, 'getRecommendations']);
    Route::get('/purchase-order-products', [ProductController::class, 'getForPurchaseOrder']);

    // Categories
    Route::get('/categories', [ProductController::class, 'getCategories']);

    // Customers
    Route::get('/customers', [CustomerController::class, 'index']);
});

