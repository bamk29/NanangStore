<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierList extends Component
{
    use WithPagination;

    public $supplierId, $name, $phone, $email, $address;
    public $isModalOpen = false;
    public $isDeleteModalOpen = false;
    public $search = '';

    protected $rules = [
        'name' => 'required|string|min:3',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email',
        'address' => 'nullable|string',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $suppliers = Supplier::where('name', 'like', '%'.$this->search.'%')
            ->latest()
            ->paginate(10);

        return view('livewire.suppliers.supplier-list', compact('suppliers'));
    }

    public function create()
    {
        $this->resetForm();
        $this->openModal();
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        $this->supplierId = $id;
        $this->name = $supplier->name;
        $this->phone = $supplier->phone;
        $this->email = $supplier->email;
        $this->address = $supplier->address;
        $this->openModal();
    }

    public function store()
    {
        $this->validate();

        Supplier::updateOrCreate(['id' => $this->supplierId], [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
        ]);

        session()->flash('message', $this->supplierId ? 'Supplier berhasil diperbarui.' : 'Supplier berhasil ditambahkan.');

        $this->closeModal();
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->supplierId = $id;
        $this->isDeleteModalOpen = true;
    }

    public function deleteSupplier()
    {
        Supplier::find($this->supplierId)->delete();
        $this->isDeleteModalOpen = false;
        session()->flash('message', 'Supplier berhasil dihapus.');
    }

    private function resetForm()
    {
        $this->supplierId = null;
        $this->name = '';
        $this->phone = '';
        $this->email = '';
        $this->address = '';
    }

    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }
}
