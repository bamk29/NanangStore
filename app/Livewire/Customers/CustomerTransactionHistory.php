<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class CustomerTransactionHistory extends Component
{
    use WithPagination;

    public Customer $customer;

    public function mount(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function render()
    {
        $transactions = Transaction::where('customer_id', $this->customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $topProducts = TransactionDetail::whereHas('transaction', function ($query) {
                $query->where('customer_id', $this->customer->id);
            })
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('COUNT(*) as frequency'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        return view('livewire.customers.customer-transaction-history', [
            'transactions' => $transactions,
            'topProducts' => $topProducts,
        ]);
    }
}
