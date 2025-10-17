<?php

namespace App\Livewire\Kasir;

use Livewire\Component;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use App\Models\Category;

class PosTransaksi extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';

    // Properti untuk manajemen pelanggan
    public $customer_search = '';
    public $showPaymentModal = false;
    public $customer_name, $payment_method = 'cash', $paid_amount = 0, $change = 0, $notes;
    public $total = 0, $transaction;
    public $cart = [];
    public $transaction_type = 'retail';

    protected $listeners = ['saveTransaction'];

    /**
     * Menyimpan transaksi dari Alpine.js ke database
     */
    public function saveTransaction($data)
    {
        $cart = $data['cart'] ?? [];
        $this->total = $data['total'] ?? 0;
        $this->customer_name = $data['customer_name'] ?? '';
        $this->payment_method = $data['payment_method'] ?? 'cash';
        $this->paid_amount = $data['paid_amount'] ?? 0;
        $this->notes = $data['notes'] ?? '';
        $this->change = max(0, $this->paid_amount - $this->total);

        if (empty($cart)) {
            $this->dispatch('alert', [
                'type' => 'warning',
                'message' => 'Keranjang masih kosong!',
            ]);
            return;
        }

        if ($this->paid_amount < $this->total) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Uang tidak cukup!',
            ]);
            return;
        }

        DB::transaction(function () use ($cart) {
            $invoiceNumber = 'INV-' . now()->format('YmdHis');

            $this->transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'customer_name' => $this->customer_name,
                'payment_method' => $this->payment_method,
                'total_amount' => $this->total,
                'paid_amount' => $this->paid_amount,
                'change_amount' => $this->change,
                'notes' => $this->notes,
                'transaction_type' => $this->transaction_type,
                'user_id' => auth()->id(),
            ]);

            foreach ($cart as $item) {
                $price = ($item['qty'] >= $item['wholesale_min_qty'] && $this->transaction_type === 'wholesale')
                    ? $item['wholesale_price']
                    : $item['price'];

                $subtotal = $price * $item['qty'];

                TransactionDetail::create([
                    'transaction_id' => $this->transaction->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'], // pastikan pakai 'qty' dari cart
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                Product::where('id', $item['id'])->decrement('stock', $item['qty']);
            }
        });

        $this->resetPaymentFields();

        $this->dispatch('cart:reset'); // Reset Alpine cart
        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Transaksi berhasil disimpan!',
        ]);
        $this->items = collect();

        return redirect()->route('kasir-bersama.invoice', $this->transaction);
    }

    private function resetPaymentFields()
    {
        $this->customer_name = '';
        $this->payment_method = 'cash';
        $this->paid_amount = 0;
        $this->change = 0;
        $this->notes = '';
        $this->total = 0;
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')->orWhere('code', 'like', '%' . $this->search . '%'))
            ->when($this->categoryFilter, fn($q) => $q->where('category_id', $this->categoryFilter))
            ->orderBy('name')
            ->paginate(20);

        $categories = Category::all();

        return view('livewire.kasir.pos-transaksi', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
