<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerList extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Properti untuk modal Tambah/Edit
    public $showModal = false;


    public $customerId;
    public $name;
    public $phone;

    // Properti untuk modal pembayaran hutang
    public $showDebtModal = false;
    public $selectedCustomer;
    public $payment_amount;

    // Properti untuk modal Hapus
    public $customerToDeleteId;

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
        $this->showDebtModal = false;
        $this->reset(['customerId', 'name', 'phone']);
        $this->selectedCustomer = null;
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
        $this->showModal = true;
    }

    public function saveCustomer()
    {
        $this->validate();

        $message = $this->customerId ? 'Data pelanggan berhasil diperbarui.' : 'Pelanggan baru berhasil ditambahkan.';

        Customer::updateOrCreate(
            ['id' => $this->customerId],
            [
                'name' => $this->name,
                'phone' => $this->phone,
            ]
        );

        $this->dispatch('show-alert', ['type' => 'success', 'message' => $message]);
        $this->showModal = false;
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

    public function openDebtModal(Customer $customer)
    {
        $this->selectedCustomer = $customer;
        $this->payment_amount = null; // Reset amount
        $this->showDebtModal = true;
    }

    public function processDebtPayment()
    {
        $this->validate([
            'payment_amount' => 'required|numeric|min:1|max:' . $this->selectedCustomer->debt
        ]);

        // 1. Kurangi hutang pelanggan
        $this->selectedCustomer->decrement('debt', $this->payment_amount);

        // 2. Catat pembayaran sebagai transaksi baru agar tercatat di laporan
        Transaction::create([
            'invoice_number' => 'DEBT-' . now()->format('YmdHis'),
            'user_id' => auth()->id(),
            'customer_id' => $this->selectedCustomer->id,
            'total_amount' => $this->payment_amount, // Totalnya adalah sebesar yang dibayar
            'paid_amount' => $this->payment_amount,  // Uang dibayar juga sama
            'change_amount' => 0,
            'payment_method' => 'cash', // Asumsi pembayaran hutang tunai
            'transaction_type' => 'debt_payment', // Tipe khusus untuk pembayaran hutang
            'notes' => 'Pembayaran hutang atas nama ' . $this->selectedCustomer->name,
        ]);

        $this->showDebtModal = false;

        $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Pembayaran hutang berhasil diproses.']);
    }

    public function render()
    {
        $customers = Customer::query()
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
