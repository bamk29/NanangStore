<?php

namespace App\Livewire\Kasir;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PosCart extends Component
{
    // Properti ini hanya untuk manajemen pelanggan via Livewire
    public $customer_search = '';
    public $customers = [];
    public $selected_customer_id;
    public $selected_customer_name;
    public $showCustomerCreateModal = false;
    public $new_customer_name = '';
    public $new_customer_phone = '';
    public $selectedCustomerModel = null;

    public $initialItems = [];
    public $initialCustomer = null;
    public $initialType = 'retail';
    public $initialPendingId = null;

    public function clearCart()
    {
        $this->reset();
        $this->customers = [];
    }

    public function mount()
    {
        // Logika mount dari Pos/Cart.php bisa disesuaikan di sini jika perlu
    }

    public function updatedCustomerSearch($value)
    {
        if (strlen($value) < 2) {
            $this->customers = [];
            return;
        }
        $this->customers = Customer::where('name', 'like', "%{$value}%")->orWhere('phone', 'like', "%{$value}%")->limit(5)->get();
    }

    public function selectCustomer($customerId, $customerName)
    {
        $this->selected_customer_id = $customerId;
        $this->selected_customer_name = $customerName;
        $this->selectedCustomerModel = Customer::find($customerId);
        $this->customer_search = '';
        $this->customers = [];
        // Kirim data pelanggan ke AlpineJS
        $this->dispatch('customer:selected', customer: $this->selectedCustomerModel->toArray());
    }

    public function clearCustomer()
    {
        $this->reset(['selected_customer_id', 'selected_customer_name', 'customer_search', 'customers', 'selectedCustomerModel']);
        $this->dispatch('customer:cleared');
    }

    public function createNewCustomer()
    {
        $validatedData = $this->validate([
            'new_customer_name' => 'required|string|min:3',
            'new_customer_phone' => 'nullable|string|unique:customers,phone',
        ]);

        $customer = Customer::create([
            'name' => $validatedData['new_customer_name'],
            'phone' => $validatedData['new_customer_phone'],
        ]);
        $this->selectCustomer($customer->id, $customer->name);
        $this->showCustomerCreateModal = false;
        $this->reset(['new_customer_name', 'new_customer_phone']);
    }

    public function processPaymentFinal($cart, $paymentDetails)
    {
        $customer = isset($paymentDetails['customer']['id']) ? Customer::find($paymentDetails['customer']['id']) : null;

        if ($paymentDetails['payment_method'] === 'debt' && !$customer) {
            return $this->dispatch('showAlert', ['type' => 'error', 'message' => 'Pelanggan harus dipilih untuk transaksi hutang.']);
        }

        try {
            DB::transaction(function () use ($cart, $paymentDetails, $customer) {
                $isUpdate = isset($paymentDetails['pending_id']) && $paymentDetails['pending_id'];
                $transaction = null;

                $transactionData = [
                    'user_id' => auth()->id(),
                    'customer_id' => $customer->id ?? null,
                    'total_amount' => $paymentDetails['final_total'],
                    'paid_amount' => $paymentDetails['paid_amount'],
                    'change_amount' => $paymentDetails['change'],
                    'payment_method' => $paymentDetails['payment_method'],
                    'transaction_type' => $paymentDetails['transaction_type'],
                    'status' => 'completed',
                    'notes' => $paymentDetails['notes'],
                ];

                if ($isUpdate) {
                    $transaction = Transaction::find($paymentDetails['pending_id']);
                    if (!$transaction) throw new \Exception('Transaksi pending tidak ditemukan.');
                    $transaction->update($transactionData);
                    $transaction->details()->delete();
                } else {
                    $transactionData['invoice_number'] = Transaction::generateInvoiceNumber();
                    $transaction = Transaction::create($transactionData);
                }

                foreach ($cart as $item) {
                    Product::find($item['id'])->decrement('stock', $item['quantity']);
                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }

                if ($customer) {
                    if ($paymentDetails['include_old_debt'] && $paymentDetails['payment_method'] !== 'debt') {
                        $customer->update(['debt' => 0]);
                    }
                    if ($paymentDetails['payment_method'] === 'debt') {
                        $customer->increment('debt', $paymentDetails['final_total']);
                    }
                    if ($paymentDetails['payment_method'] !== 'debt') {
                        $points_earned = floor($paymentDetails['subtotal'] / 10000);
                        if ($points_earned > 0) $customer->increment('points', $points_earned);
                    }
                }

                $this->dispatch('transaction-saved', id: $transaction->id);
            });

            $this->clearCart();
        } catch (\Exception $e) {
            $this->dispatch('showAlert', ['type' => 'error', 'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()]);
        }
    }

    public function holdTransaction($cart, $paymentDetails)
    {
        if (empty($cart)) {
            return $this->dispatch('showAlert', ['type' => 'error', 'message' => 'Keranjang kosong.']);
        }

        $customer = isset($paymentDetails['customer']['id']) ? Customer::find($paymentDetails['customer']['id']) : null;

        $transaction = Transaction::create([
            'invoice_number' => 'PEND-' . now()->format('YmdHis'),
            'user_id' => auth()->id(),
            'customer_id' => $customer->id ?? null,
            'total_amount' => $paymentDetails['subtotal'],
            'paid_amount' => 0,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'transaction_type' => $paymentDetails['transaction_type'],
            'status' => 'pending',
            'notes' => $paymentDetails['notes'],
        ]);

        foreach ($cart as $item) {
            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['subtotal'],
            ]);
        }

        $this->dispatch('showAlert', ['type' => 'success', 'message' => 'Transaksi berhasil disimpan sebagai Tertunda.']);
        $this->dispatch('cart:reset');
        $this->clearCart();
    }

    public function render()
    {
        return view('livewire.kasir.pos-cart');
    }
}