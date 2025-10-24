<?php

namespace App\Livewire\Products;

use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;

class ProductImport extends Component
{
    use WithFileUploads;

    public $file;
    public $importing = false;
    public $importFinished = false;

    protected $rules = [
        'file' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB Max
    ];

    public function import()
    {
        $this->validate();

        $this->importing = true;

        try {
            Excel::import(new ProductsImport, $this->file);
            $this->importFinished = true;
            $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Produk berhasil diimpor!']);
        } catch (\Exception $e) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Gagal mengimpor produk: ' . $e->getMessage()]);
        } finally {
            $this->importing = false;
            $this->file = null; // Clear the file input
        }
    }

    public function render()
    {
        return view('livewire.products.product-import');
    }
}
