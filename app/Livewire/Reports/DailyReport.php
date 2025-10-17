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

        $transactionsQuery = Transaction::with('details.product')
            ->whereDate('created_at', $date)
            ->where('status', 'completed');

        // Terapkan filter toko/kategori
        if ($this->storeFilter === 'bakso') {
            $transactionsQuery->whereHas('details.product', function ($q) {
                $q->where('category_id', 1);
            });
        } elseif ($this->storeFilter === 'nanang_store') {
            $transactionsQuery->whereHas('details.product', function ($q) {
                $q->where('category_id', '!=', 1);
            });
        }

        $transactions = $transactionsQuery->get();

        $totalSales = 0;
        $totalProfit = 0;
        $salesByPaymentMethod = [
            'cash' => 0,
            'transfer' => 0,
            'debt' => 0,
        ];

        $productPerformance = collect();

        foreach ($transactions as $transaction) {
            // Kalkulasi untuk ringkasan atas (total & per metode bayar)
            if (array_key_exists($transaction->payment_method, $salesByPaymentMethod)) {
                $salesByPaymentMethod[$transaction->payment_method] += $transaction->total_amount;
            }
            $totalSales += $transaction->total_amount;

            // Agregasi data per produk
            foreach ($transaction->details as $detail) {
                if (!$detail->product) continue;

                // Double check filter jika transaksi bisa berisi produk campuran
                if ($this->storeFilter === 'bakso' && $detail->product->category_id != 1) continue;
                if ($this->storeFilter === 'nanang_store' && $detail->product->category_id == 1) continue;

                $sellingPrice = $detail->price;
                $costPrice = $detail->product->cost_price ?? 0;
                $quantity = $detail->quantity;
                $profit = ($sellingPrice - $costPrice) * $quantity;
                $totalProfit += $profit;

                $productId = $detail->product_id;
                $item = $productPerformance->get($productId, [
                    'product_name' => $detail->product->name,
                    'total_quantity' => 0,
                    'total_sales' => 0,
                    'total_profit' => 0,
                    'cost_price' => $costPrice,
                    'avg_selling_price' => 0,
                    'prices' => [],
                ]);

                $item['total_quantity'] += $quantity;
                $item['total_sales'] += $detail->subtotal;
                $item['total_profit'] += $profit;
                $item['prices'][] = $sellingPrice;

                $productPerformance->put($productId, $item);
            }
        }

        // Finalisasi data produk & hitung harga jual rata-rata
        $this->productSalesData = $productPerformance->map(function ($item) {
            if (count($item['prices']) > 0) {
                $item['avg_selling_price'] = collect($item['prices'])->avg();
            }
            unset($item['prices']);
            return $item;
        })->sortByDesc('total_profit')->values()->all();

        $this->summary = [
            'total_sales' => $totalSales,
            'total_profit' => $totalProfit,
            'total_transactions' => $transactions->count(),
            'sales_by_payment' => $salesByPaymentMethod,
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
