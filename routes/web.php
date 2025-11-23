<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

use App\Livewire\Reports\ProductReport;
use App\Livewire\PurchaseOrders\PurchaseOrderList;
use App\Livewire\PurchaseOrders\PurchaseOrderForm;
use App\Livewire\Orders\PhoneOrderManager;
use App\Http\Controllers\Orders\PhoneOrderController;
use App\Livewire\Reports\TransactionHistory;
use App\Livewire\PurchaseOrders\TestComponent;
use App\Livewire\Transactions\PendingList;
use App\Livewire\Customers\CustomerList;
use App\Livewire\Reports\SalesBakso;
use App\Livewire\Reports\DailyReport;
use App\Livewire\Reports\DebtReport;
use App\Livewire\Reports\SalesNanangStore;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Suppliers\SupplierList;
use App\Livewire\Reports\InventoryReport;
use App\Livewire\Dashboard;
use App\Livewire\Reports\TransactionReport;
use App\Livewire\StockAdjustments\StockAdjustmentList;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});


Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/orders/print-today', [OrderController::class, 'printToday'])->name('orders.print-today');



Route::get('/pending', PendingList::class)
    ->middleware(['auth'])
    ->name('transactions.pending');

Route::get('/sales-bakso', SalesBakso::class)
    ->middleware(['auth'])
    ->name('reports.sales-bakso');



Route::get('/customers', CustomerList::class)
    ->middleware(['auth'])
    ->name('customers.index');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile.edit');

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

Route::middleware('auth')->group(function () {
    // Temporary Test Route


    // POS Routes
    Route::prefix('pos')->group(function () {
        Route::get('/', App\Livewire\Pos\PosIndex::class)->name('pos.index');
        Route::get('/invoice/{transaction}', App\Livewire\Pos\Invoice::class)->name('pos.invoice');
    });

    // Print Route (stand-alone)
    Route::get('/print/receipt-direct', [App\Http\Controllers\PrintController::class, 'printDirect'])->name('print.direct');

    // Rute untuk mencetak pesanan (orders)
    Route::post('/print/order/recap', [OrderController::class, 'printToday'])->name('print.dailyRecap');
    Route::post('/print/order/{order}', [OrderController::class, 'printOrder'])->name('print.order');
    // Product Management Routes
    Route::prefix('products')->group(function () {
        Route::get('/', App\Livewire\Products\ProductList::class)->name('products.index');
        Route::get('/create', App\Livewire\Products\CreateProduct::class)->name('products.create');
        Route::get('/import', App\Livewire\Products\ProductImport::class)->name('products.import'); // New route
        Route::get('/{product}/edit', App\Livewire\Products\EditProduct::class)->name('products.edit');
    });

    // Category Management Routes
    Route::prefix('categories')->group(function () {
        Route::get('/', App\Livewire\Categories\CategoryList::class)->name('categories.index');
        Route::get('/create', App\Livewire\Categories\CreateCategory::class)->name('categories.create');
        Route::get('/edit/{category_id}', App\Livewire\Categories\CreateCategory::class)->name('categories.edit');
    });

    // Supplier Routes
    Route::get('/suppliers', SupplierList::class)->name('suppliers.index');

    // User Management Route
    Route::get('/users', App\Livewire\UserManagement::class)->name('users.index');

    // Purchase Order Routes
    Route::prefix('purchase-orders')->group(function () {
        Route::get('/', PurchaseOrderList::class)->name('purchase-orders.index');
        Route::get('/create', PurchaseOrderForm::class)->name('purchase-orders.create');
        Route::get('/{orderId}/edit', PurchaseOrderForm::class)->name('purchase-orders.edit');
    });

    // Goods Receipt Routes
    Route::prefix('goods-receipts')->group(function () {
        Route::get('/', \App\Livewire\GoodsReceipts\GoodsReceiptList::class)->name('goods-receipts.index');
        Route::get('/create', \App\Livewire\GoodsReceipts\GoodsReceiptForm::class)->name('goods-receipts.create');
        Route::get('/{receiptId}/edit', \App\Livewire\GoodsReceipts\GoodsReceiptForm::class)->name('goods-receipts.edit');
    });

    // Goods Return Routes
    Route::prefix('goods-returns')->group(function () {
        Route::get('/', \App\Livewire\GoodsReturns\GoodsReturnList::class)->name('goods-returns.index');
        Route::get('/create', \App\Livewire\GoodsReturns\GoodsReturnForm::class)->name('goods-returns.create');
        Route::get('/{returnId}/edit', \App\Livewire\GoodsReturns\GoodsReturnForm::class)->name('goods-returns.edit');
    });

    // Inventory Management
    Route::prefix('inventory')->group(function () {
        Route::get('/adjustments', StockAdjustmentList::class)->name('stock-adjustments.index');
    });

    // Report Routes
    Route::prefix('reports')->group(function () {
        Route::get('/sales', App\Livewire\Reports\SalesReport::class)->name('reports.sales');
        Route::get('/sales-nanang-store', SalesNanangStore::class)->name('reports.sales-nanang-store');
        Route::get('/daily', DailyReport::class)->name('reports.daily');
        Route::get('/debt', DebtReport::class)->name('reports.debt');
        Route::get('/product', ProductReport::class)->name('reports.product');
        Route::get('/reports/inventory', InventoryReport::class)->name('reports.inventory');
        Route::get('/reports/transaction-history', TransactionHistory::class)->name('reports.transaction-history');
        Route::get('/reports/daily-profit', \App\Livewire\Reports\DailyProfitReport::class)->name('reports.daily-profit');
        Route::get('/reports/today-transaction', \App\Livewire\Reports\TodayTransaction::class)->name('reports.today-transaction');
        Route::get('/transaction', TransactionReport::class)->name('reports.transaction');
    });

    Route::prefix('laporan')->middleware('auth')->group(function () {
        Route::get('/nanang-store', \App\Livewire\Financials\NanangStoreDashboard::class)->name('financials.nanang-store');
        Route::get('/giling-bakso', \App\Livewire\Financials\GilingBaksoDashboard::class)->name('financials.giling-bakso');
    });

    //purchase-orders
    Route::get('/purchase-orders', PurchaseOrderList::class)->name('purchase-orders.index');

    // Phone Orders
    Route::get('/orders/phone', PhoneOrderManager::class)->name('phone-orders.index');
    Route::get('/orders/phone/{order}/print', [PhoneOrderController::class, 'print'])->name('phone-orders.print');
});


Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');
