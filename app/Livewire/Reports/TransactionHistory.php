<?php

namespace App\Livewire\Reports;

use App\Models\Transaction;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class TransactionHistory extends Component
{
    use WithPagination;

    public $selectedDate;
    public $search = '';

    public $showPaymentModal = false;
    public ?Transaction $editingTransaction = null;
    public $payment_method;
    public $paid_amount;
    public $notes;

    public function mount()
    {
        $this->selectedDate = Carbon::now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatedSelectedDate()
    {
        $this->resetPage();
    }

    public function render()
    {
        $transactions = Transaction::with(['customer', 'user'])
            ->whereDate('created_at', $this->selectedDate)
            ->where(function ($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->latest()
            ->paginate(20);

        return view('livewire.reports.transaction-history', [
            'transactions' => $transactions,
        ]);
    }

    public function cancelTransaction($transactionId)
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($transactionId) {
                $transaction = Transaction::with('details.product', 'customer')->findOrFail($transactionId);

                // 1. Hanya batalkan transaksi yang sudah selesai
                if ($transaction->status !== 'completed') {
                    throw new \Exception("Hanya transaksi yang sudah selesai yang bisa dibatalkan.");
                }

                // 2. Kembalikan Stok & Kurangi Popularitas
                foreach ($transaction->details as $detail) {
                    if ($product = $detail->product) {
                        $product->increment('stock', $detail->quantity);

                        // Hitung ulang stok boks jika ada
                        if ($product->units_in_box > 0) {
                            $product->box_stock = floor($product->stock / $product->units_in_box);
                            $product->save();
                        }
                        
                        // Kurangi usage count
                        \App\Models\ProductUsage::where('product_id', $product->id)->decrement('usage_count');
                    }
                }

                // 3. Kembalikan Hutang & Poin Pelanggan
                if ($customer = $transaction->customer) {
                    // THE CORRECT REVERSAL LOGIC:
                    // Previous Debt = Current Debt - Goods Value + Amount Paid
                    $goodsValue = (float) $transaction->details->sum('subtotal');
                    $customer->debt = (float) $customer->debt - $goodsValue + (float) $transaction->paid_amount;
                    $customer->save();

                    // Revert points if they were earned on this transaction.
                    $debtChangeOnSale = $goodsValue - (float) $transaction->paid_amount;
                    if ($debtChangeOnSale <= 0 && $transaction->payment_method !== 'debt') {
                        $pointsEarned = floor($goodsValue / 10000);
                        if ($pointsEarned > 0) {
                            $customer->decrement('points', $pointsEarned);
                        }
                    }
                }

                // 4. Hapus Catatan Keuangan Terkait
                \App\Models\FinancialTransaction::where('transaction_id', $transaction->id)->delete();

                // 5. Ubah Status Transaksi
                $transaction->status = 'cancelled';
                $transaction->save();
            });

            session()->flash('success', 'Transaksi berhasil dibatalkan.');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }

    public function openPaymentModal($transactionId)
    {
        $this->editingTransaction = Transaction::find($transactionId);
        if ($this->editingTransaction) {
            $this->payment_method = $this->editingTransaction->payment_method;
            $this->paid_amount = $this->editingTransaction->paid_amount;
            $this->notes = $this->editingTransaction->notes;
            $this->showPaymentModal = true;
        }
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->editingTransaction = null;
    }

    public function updatePayment()
    {
        $this->validate([
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer,debt',
        ]);

        if (!$this->editingTransaction) {
            session()->flash('error', 'Transaksi tidak ditemukan untuk diedit.');
            return;
        }

        try {
            DB::transaction(function () {
                $transaction = Transaction::with('customer', 'details.product')->findOrFail($this->editingTransaction->id);
                $customer = $transaction->customer;

                // 1. Reverse old financial state
                if ($customer) {
                    $oldDebtIncurred = $transaction->total_amount - $transaction->paid_amount;
                    $customer->decrement('debt', $oldDebtIncurred);
                }
                FinancialTransaction::where('transaction_id', $transaction->id)->delete();

                // 2. Apply new financial state
                $newPaidAmount = (float) $this->paid_amount;
                $newPaymentMethod = $this->payment_method;

                $transaction->paid_amount = $newPaidAmount;
                $transaction->payment_method = $newPaymentMethod;
                $transaction->notes = $this->notes;
                $transaction->save();

                if ($customer) {
                    $newDebtIncurred = $transaction->total_amount - $newPaidAmount;
                    if ($newDebtIncurred > 0) {
                        $customer->increment('debt', $newDebtIncurred);
                    } else {
                        // If overpaid, it might reduce existing debt, but we handle that separately for clarity.
                        // For now, just ensure debt doesn't go negative from this transaction alone.
                    }
                }

                if ($newPaidAmount > 0 && $newPaymentMethod !== 'debt') {
                    FinancialTransaction::create([
                        'type' => 'income',
                        'amount' => $newPaidAmount,
                        'description' => '[EDIT] Pembayaran dari penjualan (Invoice: ' . $transaction->invoice_number . ')',
                        'transaction_date' => $transaction->created_at,
                        'category' => optional(optional($transaction->details->first())->product)->category_id == 1 ? 'Giling Bakso' : 'Nanang Store',
                        'transaction_id' => $transaction->id,
                    ]);
                }
            });

            $this->closePaymentModal();
            session()->flash('success', 'Detail pembayaran berhasil diperbarui.');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui pembayaran: ' . $e->getMessage());
        }
    }

    public function correctTransaction($transactionId)
    {
        // Find the transaction *before* cancelling it
        $transaction = Transaction::with('details.product', 'customer')->findOrFail($transactionId);

        // Cancel the transaction (using existing logic)
        try {
            $this->cancelTransaction($transactionId);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memulai koreksi: ' . $e->getMessage());
            return;
        }

        session()->flash('info', 'Transaksi siap untuk dikoreksi. Silakan perbaiki dan selesaikan pembayaran.');

        // Redirect with a query parameter, exactly like the 'resume' feature
        return $this->redirect(route('pos.index', ['correct' => $transactionId]), navigate: true);
    }
}