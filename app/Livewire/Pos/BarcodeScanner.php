<?php

namespace App\Livewire\Pos;

use App\Models\Product;
use Livewire\Component;

class BarcodeScanner extends Component
{
    public $isScanning = false;

    protected $listeners = ['barcodeDetected'];

    public function startScanning()
    {
        $this->isScanning = true;
    }

    public function stopScanning()
    {
        $this->isScanning = false;
    }

    public function barcodeDetected($barcode)
    {
        $product = Product::where('code', $barcode)->first();

        if ($product) {
            $this->dispatch('productScanned', $product->id);
            $this->stopScanning();
        } else {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'Product not found'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.pos.barcode-scanner');
    }
}
