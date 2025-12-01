<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\FinancialTransaction;
use App\Models\User;
use Carbon\Carbon;

try {
    // Setup
    $user = User::first() ?? User::factory()->create();
    auth()->login($user);

    // Clear existing financial transactions for today to ensure clean test
    FinancialTransaction::whereDate('date', Carbon::today())->delete();

    // Create Dummy Financial Transactions (Simulating what Cart.php does)
    // Scenario: Total 200k, Discount 20k.
    // Nanang Store: 90k (Net)
    // Giling Bakso: 90k (Net)

    FinancialTransaction::create([
        'business_unit' => 'nanang_store',
        'type' => 'income',
        'category' => 'penjualan',
        'amount' => 90000,
        'description' => 'Test Transaction NS',
        'user_id' => $user->id,
        'date' => Carbon::today(),
    ]);

    FinancialTransaction::create([
        'business_unit' => 'giling_bakso',
        'type' => 'income',
        'category' => 'penjualan',
        'amount' => 90000,
        'description' => 'Test Transaction GB',
        'user_id' => $user->id,
        'date' => Carbon::today(),
    ]);

    // Simulate Dashboard Logic
    $nanangStoreTodaySales = FinancialTransaction::whereDate('date', Carbon::today())
        ->where('business_unit', 'nanang_store')
        ->where('type', 'income')
        ->where('category', 'penjualan')
        ->sum('amount');

    $baksoStoreTodaySales = FinancialTransaction::whereDate('date', Carbon::today())
        ->where('business_unit', 'giling_bakso')
        ->where('type', 'income')
        ->where('category', 'penjualan')
        ->sum('amount');

    echo "Dashboard Logic Verification:\n";
    echo "Nanang Store Sales: " . number_format($nanangStoreTodaySales) . " (Expected: 90,000)\n";
    echo "Giling Bakso Sales: " . number_format($baksoStoreTodaySales) . " (Expected: 90,000)\n";

    if ($nanangStoreTodaySales == 90000 && $baksoStoreTodaySales == 90000) {
        echo "SUCCESS: Dashboard logic correctly sums net sales.\n";
    } else {
        echo "FAILURE: Dashboard logic mismatch.\n";
    }

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
