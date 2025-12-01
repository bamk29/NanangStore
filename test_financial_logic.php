<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Transaction;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

try {
    // Setup
    $user = User::first() ?? User::factory()->create();
    auth()->login($user);

    // Create products
    $product1 = Product::create([
        'name' => 'Product A (Giling Bakso)',
        'code' => 'PROD-GB-' . rand(1000, 9999),
        'retail_price' => 100000,
        'wholesale_price' => 90000,
        'wholesale_min_qty' => 10,
        'unit_cost' => 80000,
        'cost_price' => 80000,
        'box_cost' => 800000,
        'units_in_box' => 10,
        'stock' => 100,
        'category_id' => 1, // Giling Bakso
    ]);

    $product2 = Product::create([
        'name' => 'Product B (Nanang Store)',
        'code' => 'PROD-NS-' . rand(1000, 9999),
        'retail_price' => 100000,
        'wholesale_price' => 90000,
        'wholesale_min_qty' => 10,
        'unit_cost' => 80000,
        'cost_price' => 80000,
        'box_cost' => 800000,
        'units_in_box' => 10,
        'stock' => 100,
        'category_id' => 2, // Nanang Store
    ]);

    // Simulate Cart
    $cart = [
        ['id' => $product1->id, 'subtotal' => 100000],
        ['id' => $product2->id, 'subtotal' => 100000],
    ];

    // Payment Details
    $totalGross = 200000;
    $reduction = 20000;
    $finalTotal = $totalGross - $reduction; // 180,000

    // Logic from Cart.php
    $reductionRatio = ($totalGross > 0) ? ($finalTotal / $totalGross) : 0;

    echo "Total Gross: " . number_format($totalGross) . "\n";
    echo "Final Total: " . number_format($finalTotal) . "\n";
    echo "Reduction Ratio: $reductionRatio\n";

    $incomeByCategory = [];
    foreach ($cart as $item) {
        $product = Product::find($item['id']);
        $businessUnit = ($product->category_id == 1) ? 'giling_bakso' : 'nanang_store';
        if (!isset($incomeByCategory[$businessUnit])) {
            $incomeByCategory[$businessUnit] = 0;
        }
        
        // Apply ratio
        $adjustedSubtotal = (float) $item['subtotal'] * $reductionRatio;
        $incomeByCategory[$businessUnit] += $adjustedSubtotal;
    }

    echo "\nCalculated Incomes:\n";
    foreach ($incomeByCategory as $unit => $amount) {
        echo "- $unit: " . number_format($amount) . "\n";
    }

    // Cleanup
    $product1->delete();
    $product2->delete();

} catch (\Throwable $e) {
    file_put_contents('error_log_financial.txt', $e->getMessage());
    echo "Error logged to error_log_financial.txt\n";
}
