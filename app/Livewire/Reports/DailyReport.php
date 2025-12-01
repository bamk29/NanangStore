<?php

namespace App\Livewire\Reports;

use App\Models\Transaction;
use Livewire\Component;
use Carbon\Carbon;

class DailyReport extends Component
{
    public $selectedDate;
    public $storeFilter = 'all'; // all, bakso, nanang_store
    public $summary = [];
    public $productSalesData = [];

    public function mount()
    {
        $this->selectedDate = Carbon::now()->format('Y-m-d');
        $this->runReport();
    }

    public function runReport()
    {
        $date = Carbon::parse($this->selectedDate);

        // 1. Get all relevant transaction *details* directly
        $detailsQuery = \App\Models\TransactionDetail::whereHas('transaction', function ($q) use ($date) {
            $q->whereDate('created_at', $date)->where('status', 'completed');
        })->with(['product', 'transaction']); // Eager load relationships

        // 2. Apply store filter
        if ($this->storeFilter === 'bakso') {
            $detailsQuery->whereHas('product', function ($q) {
                $q->where('category_id', 1);
            });
        } elseif ($this->storeFilter === 'nanang_store') {
            $detailsQuery->whereHas('product', function ($q) {
                $q->where('category_id', '!=', 1);
            });
        }

        $filteredDetails = $detailsQuery->get();

        // 3. Calculate summaries from the filtered details
        $totalSales = 0;
        $totalCost = 0;
        $totalProfit = 0;

        // 5. Calculate product performance (Moved up to combine loops for efficiency)
        $productPerformance = collect();

        foreach ($filteredDetails as $detail) {
            // Calculate Reduction Ratio for this transaction
            $transaction = $detail->transaction;
            $grossTotal = $transaction->total_amount + $transaction->total_reduction_amount;
            $ratio = $grossTotal > 0 ? ($transaction->total_amount / $grossTotal) : 1; // Default to 1 if no gross total (shouldn't happen for valid sales)

            // Apply ratio to subtotal
            $adjustedSubtotal = $detail->subtotal * $ratio;
            $adjustedPrice = $detail->price * $ratio;

            $totalSales += $adjustedSubtotal;

            if ($detail->product) {
                $cost = $detail->product->cost_price * $detail->quantity;
                $totalCost += $cost;
                
                $profit = $adjustedSubtotal - $cost;
                $totalProfit += $profit;

                // Product Performance Data
                $productId = $detail->product_id;
                $item = $productPerformance->get($productId, [
                    'product_name' => $detail->product->name,
                    'remaining_stock' => $detail->product->stock,
                    'total_quantity' => 0,
                    'total_sales' => 0,
                    'total_profit' => 0,
                    'cost_price' => $detail->product->cost_price ?? 0,
                    'avg_selling_price' => 0,
                    'prices' => [],
                ]);

                $item['total_quantity'] += $detail->quantity;
                $item['total_sales'] += $adjustedSubtotal;
                $item['total_profit'] += $profit;
                $item['prices'][] = $adjustedPrice;

                $productPerformance->put($productId, $item);
            }
        }

        $this->productSalesData = $productPerformance->map(function ($item) {
            if (count($item['prices']) > 0) {
                $item['avg_selling_price'] = collect($item['prices'])->avg();
            }
            unset($item['prices']);
            return $item;
        })->sortByDesc('total_profit')->values()->all();

        // 4. Group details by transaction to calculate payment methods (Re-implemented to use adjusted values)
        // Note: This is slightly complex because we need to sum the *adjusted* subtotals per transaction
        // But we already have the adjusted subtotals calculated above.
        // Simpler approach: Iterate through details again or aggregate during the first loop.
        // Let's aggregate during the first loop.
        
        $salesByPaymentMethod = [
            'cash' => 0,
            'transfer' => 0,
            'debt' => 0,
        ];
        
        // Track total discounts given based on allocation flags
        $totalDiscount = 0;
        $transactionDiscountData = []; // Store transaction-level data for discount calculation

        foreach ($filteredDetails as $detail) {
             // Calculate Reduction Ratio (Duplicate logic, but safe)
            $transaction = $detail->transaction;
            $transactionId = $transaction->id;
            $grossTotal = $transaction->total_amount + $transaction->total_reduction_amount;
            $ratio = $grossTotal > 0 ? ($transaction->total_amount / $grossTotal) : 1;
            $adjustedSubtotal = $detail->subtotal * $ratio;

            $paymentMethod = $transaction->payment_method;
            if (array_key_exists($paymentMethod, $salesByPaymentMethod)) {
                $salesByPaymentMethod[$paymentMethod] += $adjustedSubtotal;
            }
            
            // Track filtered items' gross subtotal per transaction for discount allocation
            if (!isset($transactionDiscountData[$transactionId])) {
                $transactionDiscountData[$transactionId] = [
                    'filtered_gross' => 0,
                    'total_reduction' => $transaction->total_reduction_amount ?? 0,
                    'discount_for_bakso' => $transaction->discount_for_bakso ?? false,
                    'discount_for_nanang' => $transaction->discount_for_nanang ?? false,
                    'transaction' => $transaction,
                ];
            }
            $transactionDiscountData[$transactionId]['filtered_gross'] += $detail->subtotal;
        }
        
        // Calculate discount based on allocation flags and store filter
        foreach ($transactionDiscountData as $data) {
            $transaction = $data['transaction'];
            $filteredGross = $data['filtered_gross'];
            $totalReduction = $data['total_reduction'];
            $discountForBakso = $data['discount_for_bakso'];
            $discountForNanang = $data['discount_for_nanang'];
            
            if ($totalReduction <= 0) {
                continue; // No discount to allocate
            }
            
            // Get transaction's total gross (all items, not just filtered)
            $transactionGross = $transaction->total_amount + $transaction->total_reduction_amount;
            
            if ($transactionGross <= 0) {
                continue;
            }
            
            // Determine discount allocation based on flags and filter
            if ($this->storeFilter === 'bakso') {
                // Filter Bakso: only count discount if it was allocated to Bakso
                if ($discountForBakso && !$discountForNanang) {
                    // 100% discount to Bakso
                    $totalDiscount += $totalReduction;
                } elseif ($discountForBakso && $discountForNanang) {
                    // Split proportionally, count Bakso's portion
                    $proportion = $filteredGross / $transactionGross;
                    $totalDiscount += $totalReduction * $proportion;
                } elseif (!$discountForBakso && !$discountForNanang) {
                    // No checkbox selected: proportional fallback
                    $proportion = $filteredGross / $transactionGross;
                    $totalDiscount += $totalReduction * $proportion;
                }
                // If only Nanang checked, Bakso gets 0 discount
                
            } elseif ($this->storeFilter === 'nanang_store') {
                // Filter Nanang Store: only count discount if it was allocated to Nanang
                if ($discountForNanang && !$discountForBakso) {
                    // 100% discount to Nanang
                    $totalDiscount += $totalReduction;
                } elseif ($discountForBakso && $discountForNanang) {
                    // Split proportionally, count Nanang's portion
                    $proportion = $filteredGross / $transactionGross;
                    $totalDiscount += $totalReduction * $proportion;
                } elseif (!$discountForBakso && !$discountForNanang) {
                    // No checkbox selected: proportional fallback
                    $proportion = $filteredGross / $transactionGross;
                    $totalDiscount += $totalReduction * $proportion;
                }
                // If only Bakso checked, Nanang gets 0 discount
                
            } else {
                // Filter "Semua": count full discount
                $totalDiscount += $totalReduction;
            }
        }

        $transactions = $filteredDetails->groupBy('transaction_id'); // Keep this for count
        
        $this->summary = [
            'total_sales' => $totalSales,
            'total_cost' => $totalCost,
            'total_profit' => $totalProfit,
            'total_transactions' => $transactions->count(),
            'sales_by_payment' => $salesByPaymentMethod,
            'total_discount' => $totalDiscount,
        ];
    }

    public function setStoreFilter($filter)
    {
        $this->storeFilter = $filter;
        $this->runReport();
    }

    public function render()
    {
        return view('livewire.reports.daily-report');
    }
}
