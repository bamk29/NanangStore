<?php

namespace App\Livewire\Pos;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Customer;
use App\Models\PhoneOrder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Cart extends Component
{
    // Properti pencarian pelanggan dihapus, akan ditangani Alpine.js
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
        $this->pending_transaction_id = null;
    }


    public function mount()
    {
        $this->clearCart();

        if (request()->has('resume')) {
            $transactionId = request()->query('resume');
            $transaction = Transaction::with('details.product', 'customer')->find($transactionId);

            if ($transaction && $transaction->status === 'pending') {
                $items = [];
                foreach ($transaction->details as $detail) {
                    // Pastikan produk masih ada untuk menghindari error
                    if ($detail->product) {
                        $items[] = [
                            'id' => $detail->product->id,
                            'name' => $detail->product->name,
                            'code' => $detail->product->code,
                            'stock' => $detail->product->stock,
                            'retail_price' => $detail->product->retail_price,
                            'wholesale_price' => $detail->product->wholesale_price,
                            'wholesale_min_qty' => $detail->product->wholesale_min_qty,
                            'quantity' => $detail->quantity,
                            'price' => $detail->price,
                            'subtotal' => $detail->subtotal
                        ];
                    }
                }

                // Set public properties for Alpine to initialize with
                $this->initialItems = $items;
                $this->initialCustomer = $transaction->customer;
                $this->initialType = $transaction->transaction_type;
                $this->initialPendingId = $transaction->id;

                if ($transaction->customer) {
                    $this->selectCustomer($transaction->customer->id, $transaction->customer->name);
                }
            }
        } elseif (request()->has('load_phone_order')) {
            $phoneOrderId = request()->query('load_phone_order');
            $phoneOrder = PhoneOrder::with('items.product', 'customer')->find($phoneOrderId);

            if ($phoneOrder && $phoneOrder->status !== 'selesai') {
                $items = [];
                foreach ($phoneOrder->items as $item) {
                    if ($item->product) {
                        $items[] = [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'code' => $item->product->code,
                            'stock' => $item->product->stock,
                            'retail_price' => $item->product->retail_price,
                            'wholesale_price' => $item->product->wholesale_price,
                            'wholesale_min_qty' => $item->product->wholesale_min_qty,
                            'quantity' => $item->quantity,
                            'price' => $item->product->retail_price, // Default to retail, Alpine will recalculate
                            'subtotal' => $item->product->retail_price * $item->quantity, // Default, Alpine will recalculate
                        ];
                    }
                }

                $this->initialItems = $items;
                $this->initialCustomer = $phoneOrder->customer;
                $this->initialType = 'retail'; // Default to retail

                if ($phoneOrder->customer) {
                    $this->selectCustomer($phoneOrder->customer->id, $phoneOrder->customer->name);
                }

                // Update status pesanan telepon
                $phoneOrder->update(['status' => 'diproses']);
            }
        } elseif (request()->has('correct')) {
            $transactionId = request()->query('correct');
            $transaction = Transaction::with('details.product', 'customer')->find($transactionId);

            if ($transaction) { // Status can be 'cancelled', which is fine
                $items = [];
                foreach ($transaction->details as $detail) {
                    if ($detail->product) {
                        $items[] = [
                            'id' => $detail->product->id,
                            'name' => $detail->product->name,
                            'code' => $detail->product->code,
                            'stock' => $detail->product->stock, // Stock will be current, which is correct
                            'retail_price' => $detail->product->retail_price,
                            'wholesale_price' => $detail->product->wholesale_price,
                            'wholesale_min_qty' => $detail->product->wholesale_min_qty,
                            'quantity' => $detail->quantity,
                            'price' => $detail->price,
                            'subtotal' => $detail->subtotal
                        ];
                    }
                }

                $this->initialItems = $items;
                $this->initialCustomer = $transaction->customer;
                $this->initialType = $transaction->transaction_type;
                $this->initialPendingId = null; // This is a new transaction, not resuming a pending one

                if ($transaction->customer) {
                    $this->selectCustomer($transaction->customer->id, $transaction->customer->name);
                }
            }
        } elseif (request()->has('customer_id')) {
            $customer = Customer::find(request()->query('customer_id'));
            if ($customer) {
                // Set the initial customer for AlpineJS, similar to the resume flow
                $this->initialCustomer = $customer;
                // Also set the Livewire properties for the UI to update correctly
                $this->selected_customer_id = $customer->id;
                $this->selected_customer_name = $customer->name;
                $this->selectedCustomerModel = $customer;
            }
        }

    }


    public function selectCustomer($customerId, $customerName)
    {
        $this->selected_customer_id = $customerId;
        $this->selected_customer_name = $customerName;
        $this->selectedCustomerModel = Customer::find($customerId);

        // Kirim data pelanggan ke AlpineJS
        $this->dispatch('customer:selected', customer: $this->selectedCustomerModel->toArray());
        $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Pelanggan berhasil dipilih.']);
    }

    public function selectCustomerFromSearch($customer)
    {
        $this->selected_customer_id = $customer['id'];
        $this->selected_customer_name = $customer['name'];
        // Convert array to a Customer model instance for consistency, if needed elsewhere
        $this->selectedCustomerModel = new Customer($customer);

        // Dispatch the event to AlpineJS with the full customer data
        $this->dispatch('customer:selected', customer: $customer);
        $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Pelanggan berhasil dipilih.']);
    }

    public function updatedCustomerSearch($value)
    {
    }

    public function clearCustomer()
    {
        $this->reset(['selected_customer_id', 'selected_customer_name', 'selectedCustomerModel']);
        $this->dispatch('customer:cleared');
    }

    public function createNewCustomer()
    {
        $validatedData = $this->validate([
            'new_customer_name' => 'required|string|min:3|unique:customers,name',
            'new_customer_phone' => 'nullable|string|unique:customers,phone',
        ]);

        // FIX: Convert empty string to NULL to avoid unique constraint violation
        $phone = !empty($validatedData['new_customer_phone']) ? $validatedData['new_customer_phone'] : null;

        $customer = Customer::create([

            'name' => $validatedData['new_customer_name'],
            'phone' => $phone,
        ]);

        $this->selectCustomer($customer->id, $customer->name);
        $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Pelanggan baru berhasil ditambahkan.']);

        $this->showCustomerCreateModal = false;
        $this->reset(['new_customer_name', 'new_customer_phone']);
    }

    /**
     * Menerima semua data dari AlpineJS dan memproses pembayaran.
     */
    public function processPaymentFinal($cart, $paymentDetails)
    {
        $customer = isset($paymentDetails['customer']['id']) ? Customer::find($paymentDetails['customer']['id']) : null;

        if ($paymentDetails['payment_method'] === 'debt' && !$customer) {
            return $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Pelanggan harus dipilih untuk transaksi hutang.']);
        }

        // Logika baru: Cek kekurangan pembayaran
        $shortfall = 0;
        if ($paymentDetails['payment_method'] !== 'debt') {
            $shortfall = $paymentDetails['final_total'] - $paymentDetails['paid_amount'];
        }

        if ($shortfall > 0 && !$customer) {
            return $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Uang kurang dan tidak ada pelanggan terpilih untuk mencatat hutang.']);
        }

        try {
            DB::transaction(function () use ($cart, $paymentDetails, $customer, $shortfall) {
                $isUpdate = isset($paymentDetails['pending_id']) && $paymentDetails['pending_id'];
                $transaction = null;

                // Data untuk create atau update
                $transactionData = [
                    'user_id' => auth()->id(),
                    'customer_id' => $customer->id ?? null,
                    'total_amount' => $paymentDetails['final_total'],
                    'paid_amount' => $paymentDetails['paid_amount'],
                    'change_amount' => $shortfall > 0 ? 0 : $paymentDetails['change'], // Jika ada kekurangan, kembalian pasti 0
                    'payment_method' => $paymentDetails['payment_method'],
                    'transaction_type' => $paymentDetails['transaction_type'],
                    'status' => 'completed',
                    'notes' => $paymentDetails['notes'],
                ];

                if ($isUpdate) {
                    $transaction = Transaction::find($paymentDetails['pending_id']);
                    if (!$transaction) {
                        throw new \Exception('Transaksi pending tidak ditemukan.');
                    }
                    $transaction->update($transactionData);
                    $transaction->details()->delete(); // Hapus detail lama
                } else {
                    $transactionData['invoice_number'] = Transaction::generateInvoiceNumber();
                    $transaction = Transaction::create($transactionData);
                }

                // Buat detail baru dan kurangi stok
                foreach ($cart as $item) {
                    $product = Product::find($item['id']);
                    if ($product) {
                        // Kurangi stok utama (satuan dasar)
                        $product->decrement('stock', $item['quantity']);

                        // Update popularitas produk
                        \App\Models\ProductUsage::incrementUsage($item['id']);

                        // Hitung ulang dan perbarui stok boks
                        if ($product->units_in_box > 0) {
                            $product->refresh(); // Ambil data stok terbaru setelah decrement
                            $newBoxStock = floor($product->stock / $product->units_in_box);
                            $product->box_stock = $newBoxStock;
                            $product->save();
                        }
                    }

                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }

                // Handle hutang & poin
                if ($customer) {
                    $initialDebt = $customer->debt;

                    // Jika checkbox "Sertakan Hutang Lama" dicentang
                    if ($paymentDetails['include_old_debt']) {
                        // Hutang baru dihitung dari total tagihan gabungan dikurangi pembayaran.
                        // Ini mencakup semua skenario: bayar lunas, bayar sebagian, atau bayar lebih.
                        $newDebt = $paymentDetails['final_total'] - $paymentDetails['paid_amount'];
                    }
                    // Jika checkbox tidak dicentang
                    else {
                        // Jika metode bayar adalah "Hutang", tambahkan seluruh tagihan baru ke hutang lama.
                        if ($paymentDetails['payment_method'] === 'debt') {
                            $newDebt = $initialDebt + $paymentDetails['subtotal'];
                        }
                        // Jika bayar cash/transfer tapi kurang
                        else {
                            $shortfall = $paymentDetails['subtotal'] - $paymentDetails['paid_amount'];
                            if ($shortfall > 0) {
                                $newDebt = $initialDebt + $shortfall;
                            } else {
                                $newDebt = $initialDebt; // Hutang tidak berubah jika bayar lunas/lebih
                            }
                        }
                    }

                    // Update hutang pelanggan, pastikan tidak negatif.
                    $customer->debt = max(0, $newDebt);
                    $customer->save();

                    // Beri poin hanya jika hutang mereka tidak bertambah pada transaksi ini
                    if ($customer->debt <= $initialDebt && $paymentDetails['payment_method'] !== 'debt') {
                        $points_earned = floor($paymentDetails['subtotal'] / 10000);
                        if ($points_earned > 0) {
                            $customer->increment('points', $points_earned);
                        }
                    }
                }

                // [FINANCIAL INTEGRATION] Pencatatan Pemasukan
                $incomeByCategory = [];
                foreach ($cart as $item) {
                    $product = Product::find($item['id']);
                    $businessUnit = ($product->category_id == 1) ? 'giling_bakso' : 'nanang_store';
                    if (!isset($incomeByCategory[$businessUnit])) {
                        $incomeByCategory[$businessUnit] = 0;
                    }
                    $incomeByCategory[$businessUnit] += $item['subtotal'];
                }

                foreach ($incomeByCategory as $unit => $amount) {
                    if ($amount > 0) {
                        \App\Models\FinancialTransaction::create([
                            'business_unit' => $unit,
                            'type' => 'income',
                            'category' => 'penjualan',
                            'amount' => $amount,
                            'description' => 'Penjualan dari invoice #' . $transaction->invoice_number,
                            'transaction_id' => $transaction->id,
                            'user_id' => auth()->id(),
                            'date' => now()->toDateString(),
                        ]);
                    }
                }

                $this->dispatch('transaction-saved', id: $transaction->id);
                $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Pembayaran berhasil.']);
                $this->clearCart(); // Bersihkan state setelah berhasil
            });


            $this->clearCart(); // Bersihkan state setelah berhasil
        } catch (\Exception $e) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()]);
        }
    }

    public function holdTransaction($cart, $paymentDetails)
    {


        if (empty($cart)) {
            return $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Keranjang kosong.']);
        }

     
        // Untuk transaksi pending, pelanggan tidak wajib, tapi dianjurkan
        $customerId = null;
        if (isset($paymentDetails['customer']) && is_array($paymentDetails['customer']) && isset($paymentDetails['customer']['id'])) {
            $customerId = $paymentDetails['customer']['id'];
        }

        $transaction = Transaction::create([
            'invoice_number' => 'PEND-' . now()->format('YmdHis'),
            'user_id' => auth()->id(),
            'customer_id' => $customerId,
            'total_amount' => $paymentDetails['subtotal'], // Hanya subtotal, karena belum ada pembayaran
            'paid_amount' => 0,
            'change_amount' => 0,
            'payment_method' => 'cash', // Default, bisa diubah nanti
            'transaction_type' => $paymentDetails['transaction_type'],
            'status' => 'pending', // Status PENTING
            'notes' => $paymentDetails['notes'],
        ]);

        foreach ($cart as $item) {
            // Stok tidak dikurangi saat transaksi ditunda, hanya saat penjualan selesai.
            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['subtotal'],
            ]);
        }

        $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Transaksi berhasil disimpan sebagai Tertunda.']);
        $this->dispatch('cart:reset'); // Kirim event ke Alpine untuk reset
        $this->clearCart();
    }

    public function render()
    {
        return view('livewire.pos.cart');
    }
}
