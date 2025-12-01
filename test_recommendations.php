<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Create a user if none exists (needed for transaction)
try {
    $user = User::first() ?? User::factory()->create();
    auth()->login($user);

    // Create a test customer with random phone to avoid unique constraint
    $customer = Customer::create([
        'name' => 'Test Customer Recommendations ' . rand(1000, 9999),
        'phone' => '08' . rand(1000000000, 9999999999),
    ]);

    // Create some products with random codes
    $product1 = Product::create([
        'name' => 'Product A (Frequent)',
        'code' => 'PROD-A-' . rand(1000, 9999),
        'retail_price' => 10000,
        'stock' => 100,
        'category_id' => 1,
    ]);

    $product2 = Product::create([
        'name' => 'Product B (Rare)',
        'code' => 'PROD-B-' . rand(1000, 9999),
        'retail_price' => 20000,
        'stock' => 100,
        'category_id' => 1,
    ]);

    // Create transactions
    // Transaction 1: Buy Product A
    $t1 = Transaction::create([
        'invoice_number' => 'INV-TEST-001-' . rand(1000, 9999),
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'total_amount' => 10000,
        'paid_amount' => 10000,
        'change_amount' => 0,
        'payment_method' => 'cash',
        'transaction_type' => 'retail',
        'status' => 'completed',
    ]);
    TransactionDetail::create([
        'transaction_id' => $t1->id,
        'product_id' => $product1->id,
        'quantity' => 1,
        'price' => 10000,
        'subtotal' => 10000,
    ]);

    // Transaction 2: Buy Product A again
    $t2 = Transaction::create([
        'invoice_number' => 'INV-TEST-002-' . rand(1000, 9999),
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'total_amount' => 10000,
        'paid_amount' => 10000,
        'change_amount' => 0,
        'payment_method' => 'cash',
        'transaction_type' => 'retail',
        'status' => 'completed',
    ]);
    TransactionDetail::create([
        'transaction_id' => $t2->id,
        'product_id' => $product1->id,
        'quantity' => 1,
        'price' => 10000,
        'subtotal' => 10000,
    ]);

    // Transaction 3: Buy Product B once
    $t3 = Transaction::create([
        'invoice_number' => 'INV-TEST-003-' . rand(1000, 9999),
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'total_amount' => 20000,
        'paid_amount' => 20000,
        'change_amount' => 0,
        'payment_method' => 'cash',
        'transaction_type' => 'retail',
        'status' => 'completed',
    ]);
    TransactionDetail::create([
        'transaction_id' => $t3->id,
        'product_id' => $product2->id,
        'quantity' => 1,
        'price' => 20000,
        'subtotal' => 20000,
    ]);

    // Call the API logic directly (simulating the route)
    $recommendations = TransactionDetail::whereHas('transaction', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        })
        ->select('product_id', DB::raw('count(*) as frequency'))
        ->with('product')
        ->groupBy('product_id')
        ->orderByDesc('frequency')
        ->limit(10)
        ->get()
        ->map(function ($item) {
            return [
                'name' => $item->product->name,
                'frequency' => $item->frequency
            ];
        });

    echo "Recommendations for {$customer->name}:\n";
    foreach ($recommendations as $rec) {
        echo "- {$rec['name']} (Bought {$rec['frequency']} times)\n";
    }

    // Clean up
    TransactionDetail::whereIn('transaction_id', [$t1->id, $t2->id, $t3->id])->delete();
    $t1->delete(); $t2->delete(); $t3->delete();
    $customer->delete();
    $product1->delete(); $product2->delete();

} catch (\Throwable $e) {
    file_put_contents('error_log.txt', $e->getMessage());
    echo "Error logged to error_log.txt\n";
}
