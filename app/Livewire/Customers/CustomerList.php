<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class CustomerList extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Properti untuk modal Tambah/Edit Terpadu
    public $showModal = false;
    public $customerId;
    public $name;
    public $phone;

    // Properti untuk modal pembayaran hutang (DIKEMBALIKAN)
    public $showDebtModal = false;
    public $selectedCustomer;
    public $payment_amount;
    public $debt_payment_method = 'cash';

    // Properti untuk modal Tambah/Edit Terpadu
    public $debt;
    public $points;
    public $initialDebt;
    public $initialPoints;
    public $adjustment_notes_debt;
    public $adjustment_notes_points;

    // Properti untuk modal Hapus
    public $customerToDeleteId;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3',
            // Saat edit, validasi unique harus mengabaikan data customer itu sendiri
            'phone' => 'nullable|string|unique:customers,phone,' . $this->customerId,
        ];
    }

    public function confirmDelete($customerId)
    {
        $this->customerToDeleteId = $customerId;
    }

    public function deleteCustomer()
    {
        $customer = Customer::withCount('transactions')->find($this->customerToDeleteId);

        if ($customer->debt > 0) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Gagal! Pelanggan ini tidak bisa dihapus karena masih memiliki hutang.']);
            $this->customerToDeleteId = null;
            return;
        }

        if ($customer->transactions_count > 0) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Gagal! Pelanggan ini tidak bisa dihapus karena sudah memiliki riwayat transaksi.']);
            $this->customerToDeleteId = null;
            return;
        }

        $customer->delete();
        $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Pelanggan berhasil dihapus.']);
        $this->customerToDeleteId = null;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['customerId', 'name', 'phone', 'debt', 'points', 'initialDebt', 'initialPoints', 'adjustment_notes_debt', 'adjustment_notes_points']);
    }



    public function showCreateModal()
    {
        $this->reset(['customerId', 'name', 'phone']);
        $this->showModal = true;
    }

    public function showEditModal(Customer $customer)
    {
        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->debt = $customer->debt;
        $this->points = $customer->points;
        $this->initialDebt = $customer->debt;   // Simpan nilai awal
        $this->initialPoints = $customer->points; // Simpan nilai awal
        $this->showModal = true;
    }

    public function saveCustomer()
    {
        $this->validate();

        DB::transaction(function () {
            $customer = Customer::updateOrCreate(
                ['id' => $this->customerId],
                [
                    'name' => $this->name,
                    'phone' => $this->phone,
                ]
            );

            // Proses penyesuaian hutang jika ada perubahan
            if ($this->debt != $this->initialDebt) {
                $debtDifference = $this->debt - $this->initialDebt;
                $type = $debtDifference > 0 ? 'increase' : 'decrease';
                $customer->updateDebt(abs($debtDifference), $type, 'Manual Adjustment');
            }

            // Proses penyesuaian poin jika ada perubahan
            if ($this->points != $this->initialPoints) {
                $pointsDifference = $this->points - $this->initialPoints;
                if ($pointsDifference > 0) {
                    $customer->increment('points', $pointsDifference);
                } else {
                    $customer->decrement('points', abs($pointsDifference));
                }
            }
        });

        $message = $this->customerId ? 'Data pelanggan berhasil diperbarui.' : 'Pelanggan baru berhasil ditambahkan.';
        $this->dispatch('show-alert', ['type' => 'success', 'message' => $message]);
        $this->closeModal();
    }

    public function openDebtModal(Customer $customer)
    {
        $this->selectedCustomer = $customer;
        $this->reset(['payment_amount', 'debt_payment_method']);
        $this->debt_payment_method = 'cash';
        $this->showDebtModal = true;
    }

    public function processDebtPayment()
    {
        $this->validate([
            'payment_amount' => 'required|numeric|min:1|max:' . $this->selectedCustomer->debt,
            'debt_payment_method' => 'required|in:cash,transfer',
        ]);

        try {
            DB::transaction(function () {
                $transaction = Transaction::create([
                    'invoice_number' => 'DEBT-' . now()->format('YmdHis'),
                    'user_id' => auth()->id(),
                    'customer_id' => $this->selectedCustomer->id,
                    'total_amount' => $this->payment_amount,
                    'paid_amount' => $this->payment_amount,
                    'change_amount' => 0,
                    'payment_method' => $this->debt_payment_method,
                    'transaction_type' => 'retail', // WORKAROUND: Using 'retail' to avoid DB schema issues.
                    'status' => 'completed',
                    'notes' => 'Pembayaran hutang atas nama ' . $this->selectedCustomer->name,
                ]);

                $this->selectedCustomer->updateDebt($this->payment_amount, 'decrease', 'Debt Payment', $transaction->id);

                \App\Models\FinancialTransaction::create([
                    'business_unit' => 'nanang_store',
                    'type' => 'income',
                    'category' => 'pembayaran_hutang',
                    'amount' => $this->payment_amount,
                    'description' => 'Pembayaran hutang dari ' . $this->selectedCustomer->name,
                    'transaction_id' => $transaction->id,
                    'user_id' => auth()->id(),
                    'date' => now()->toDateString(),
                ]);
            });

            $this->showDebtModal = false;
            $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Pembayaran hutang berhasil diproses.']);

        } catch (\Exception $e) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $customers = Customer::query()
            ->withCount('transactions')
            ->withMax('transactions', 'created_at')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        return view('livewire.customers.customer-list', [
            'customers' => $customers
        ]);
    }
}
