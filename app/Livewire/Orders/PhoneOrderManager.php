<?php

namespace App\Livewire\Orders;

use App\Models\Customer;
use App\Models\PhoneOrder;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PhoneOrderManager extends Component
{
    use WithPagination;

    // Filter & Search
    public $filterDate;
    public $search = '';
    public $statusFilter = '';

    // Modal Properties
    public $showModal = false;
    public $orderId = null;
    public $customer_id, $notes, $status;
    public $items = [];

    // Customer Search
    public $customer_search = '';
    public $customers = [];
    public $selected_customer_name;

    // Product Search
    public $product_search = '';
    public $search_results = [];

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'notes' => 'nullable|string|max:500',
        'status' => 'required|in:baru,diproses,selesai,dibatalkan',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|numeric|min:0.001',
    ];

    public function mount()
    {
        $this->filterDate = Carbon::now()->format('Y-m-d');
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
        $this->customer_id = $customerId;
        $this->selected_customer_name = $customerName;
        $this->customers = [];
        $this->customer_search = '';
    }

    public function updatedProductSearch($value)
    {
        if (empty($value)) {
            $this->search_results = [];
            return;
        }
        $this->search_results = Product::where('name', 'like', '%' . $value . '%')
            ->whereNotIn('id', array_column($this->items, 'product_id'))
            ->limit(5)
            ->get();
    }

    public function addProduct($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        $this->items[] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
        ];

        $this->product_search = '';
        $this->search_results = [];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function openModal()
    {
        $this->resetModal();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    private function resetModal()
    {
        $this->orderId = null;
        $this->customer_id = null;
        $this->selected_customer_name = null;
        $this->notes = '';
        $this->items = [];
        $this->status = 'baru';
    }

    public function saveOrder()
    {
        $this->validate();

        DB::transaction(function () {
            $data = [
                'customer_id' => $this->customer_id,
                'notes' => $this->notes,
            ];

            if ($this->orderId) {
                // Update existing order
                $order = PhoneOrder::find($this->orderId);
                $data['status'] = $this->status;
                $order->update($data);
                $order->items()->delete();
            } else {
                // Create new order
                $data['status'] = 'baru';
                $order = PhoneOrder::create($data);
            }

            foreach ($this->items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }
        });

        session()->flash('message', 'Pesanan berhasil disimpan.');
        $this->closeModal();
    }

    public function editOrder($orderId)
    {
        $order = PhoneOrder::with('items.product', 'customer')->findOrFail($orderId);

        $this->orderId = $order->id;
        $this->customer_id = $order->customer_id;
        $this->selected_customer_name = $order->customer->name;
        $this->notes = $order->notes;
        $this->status = $order->status;

        $this->items = $order->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        $this->showModal = true;
    }

    public function printFilteredOrders()
    {
        $ordersToPrint = PhoneOrder::query()
            ->whereDate('created_at', $this->filterDate)
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, function ($query) {
                $query->where('id', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->get();

        if ($ordersToPrint->isEmpty()) {
            return; // Nothing to print
        }

        $orderIds = $ordersToPrint->pluck('id');

        // Update status for the orders
        PhoneOrder::whereIn('id', $orderIds)->update(['status' => 'diproses']);

        // Build URL with specific IDs
        $queryParams = http_build_query(['ids' => $orderIds->toArray()]);
        $printUrl = route('orders.print-today') . '?' . $queryParams;

        $this->dispatch('open-new-tab', $printUrl);
    }

    public function printOrderAndUpdateStatus($orderId)
    {
        $order = PhoneOrder::find($orderId);
        if ($order) {
            $order->update(['status' => 'diproses']);
            $this->dispatch('open-new-tab', route('phone-orders.print', $order->id));
        }
    }
    
    public function deleteOrder($orderId)
    {
        $order = PhoneOrder::find($orderId);
        if ($order) {
            $order->delete();
            session()->flash('message', 'Pesanan berhasil dihapus.');
        }
    }

    public function processToPos($orderId)
    {
        $order = PhoneOrder::find($orderId);
        if ($order) {
            $order->update(['status' => 'diproses']);
            return redirect()->route('pos.index', ['load_phone_order' => $orderId]);
        }
    }

    public function render()
    {
        $orders = PhoneOrder::with('customer', 'items')
            ->whereDate('created_at', $this->filterDate)
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, function ($query) {
                $query->where('id', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.orders.phone-order-manager', [
            'orders' => $orders
        ]);
    }
}