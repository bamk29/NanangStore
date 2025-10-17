<div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-semibold">Daftar Pesanan Masuk</h2>
            <div class="flex space-x-2">
                <button wire:click="printFilteredOrders" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Cetak Sesuai Filter</button>
                <button wire:click="openModal" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Buat Pesanan Baru</button>
            </div>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
                <label for="filterDate" class="block text-sm font-medium text-gray-700">Tanggal</label>
                <input type="date" id="filterDate" wire:model.live="filterDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="statusFilter" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="statusFilter" wire:model.live="statusFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Semua</option>
                    <option value="baru">Baru</option>
                    <option value="diproses">Diproses</option>
                    <option value="selesai">Selesai</option>
                    <option value="dibatalkan">Dibatalkan</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700">Cari (ID Pesanan / Nama Pelanggan)</label>
                <input type="text" id="search" wire:model.live.debounce.300ms="search" placeholder="Ketik untuk mencari..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
        </div>

        <!-- Orders Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Item</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium">#{{ $order->id }}</td>
                            <td class="px-6 py-4 text-sm">{{ $order->customer->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">{{ $order->created_at->format('H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-center">{{ $order->items->count() }}</td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusClass = match($order->status) {
                                        'baru' => 'bg-blue-100 text-blue-800',
                                        'diproses' => 'bg-yellow-100 text-yellow-800',
                                        'selesai' => 'bg-green-100 text-green-800',
                                        'dibatalkan' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                                <button wire:click="printOrderAndUpdateStatus({{ $order->id }})" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                    Cetak
                                </button>
                                <button wire:click="editOrder({{ $order->id }})" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-yellow-600 hover:bg-yellow-700">
                                    Edit
                                </button>
                                <button wire:click="processToPos({{ $order->id }})" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                    Proses ke Kasir
                                </button>
                                <button wire:click="deleteOrder({{ $order->id }})" onclick="confirm('Anda yakin ingin menghapus pesanan ini?') || event.stopImmediatePropagation()" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-red-600 hover:bg-red-700">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada pesanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    </div>

    <!-- Create/Edit Modal -->
    @if ($showModal)
    <div class="fixed z-10 inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <form wire:submit.prevent="saveOrder">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">{{ $orderId ? 'Edit' : 'Buat' }} Pesanan</h3>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Customer Search -->
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700">Pelanggan <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.live.debounce.300ms="customer_search" placeholder="Cari pelanggan..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('customer_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                @if($selected_customer_name)
                                    <div class="mt-1 text-sm">Terpilih: <span class="font-semibold">{{ $selected_customer_name }}</span></div>
                                @endif
                                @if(!empty($customers))
                                    <ul class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 max-h-40 overflow-y-auto">
                                        @foreach($customers as $customer)
                                            <li wire:click="selectCustomer({{ $customer->id }}, '{{ $customer->name }}')" class="px-4 py-2 cursor-pointer hover:bg-gray-100">{{ $customer->name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Catatan</label>
                                <textarea wire:model="notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            </div>

                            @if ($orderId)
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status Pesanan</label>
                                <select id="status" wire:model="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="baru">Baru</option>
                                    <option value="diproses">Diproses</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="dibatalkan">Dibatalkan</option>
                                </select>
                            </div>
                            @endif
                        </div>

                        <!-- Product Search & Items -->
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700">Tambah Produk</label>
                            <div class="relative">
                                 <input type="text" wire:model.live.debounce.300ms="product_search" placeholder="Cari produk..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                 @if(!empty($search_results))
                                    <ul class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 max-h-40 overflow-y-auto">
                                        @foreach($search_results as $product)
                                            <li wire:click="addProduct({{ $product->id }})" class="px-4 py-2 cursor-pointer hover:bg-gray-100">{{ $product->name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                             @error('items') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Items List (Responsive) -->
                        <div class="mt-4 max-h-60 overflow-y-auto pr-2">
                            <div class="space-y-2">
                                @forelse($items as $index => $item)
                                    <div class="flex items-center justify-between bg-gray-50 p-2 rounded-lg">
                                        <div class="flex-grow pr-4">
                                            <p class="text-sm font-medium text-gray-900">{{ $item['product_name'] }}</p>
                                        </div>
                                        <div class="w-24">
                                            <input type="number" step="any" wire:model="items.{{$index}}.quantity" class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm text-center">
                                        </div>
                                        <div class="w-16 text-right">
                                            <button wire:click="removeItem({{$index}})" type="button" class="text-red-500 hover:text-red-700">Hapus</button>
                                        </div>
                                    </div>
                                @empty
                                     <div class="text-center py-4 text-sm text-gray-500">Belum ada produk ditambahkan.</div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                        <button wire:click="closeModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        @this.on('open-new-tab', (url) => {
            const newTab = window.open(url, '_blank');
            if (!newTab || newTab.closed || typeof newTab.closed == 'undefined') {
                alert('Gagal membuka tab baru. Mohon nonaktifkan pemblokir pop-up untuk situs ini dan coba lagi.');
            }
        });
    });
</script>
