<div>
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-bold text-gray-900">{{ $orderId ? 'Edit Purchase Order' : 'Buat Purchase Order Baru' }}</h1>
                <p class="mt-2 text-sm text-gray-700">Lakukan pemesanan barang ke supplier.</p>
            </div>
        </div>

        <div class="mt-8 bg-white p-6 rounded-lg shadow-lg">
            <!-- PO Header -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="supplier" class="block text-sm font-medium text-gray-700">Supplier</label>
                    <select id="supplier" wire:model="supplier_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Pilih Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="order_date" class="block text-sm font-medium text-gray-700">Tanggal Order</label>
                    <input type="date" id="order_date" wire:model="order_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('order_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" wire:model="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="draft">Draft</option>
                        <option value="ordered">Ordered</option>
                        <option value="received">Received</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- Product Search & Items Table -->
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900">Item Pesanan</h3>
                @error('items') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <div class="mt-4 flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <div class="space-y-4">
                                @foreach($items as $index => $item)
                                    <div class="bg-gray-50 p-4 rounded-lg border">
                                        <div class="flex items-start justify-between">
                                            <h4 class="font-semibold text-gray-800">{{ $item['product_name'] }}</h4>
                                            <button wire:click.prevent="removeItem({{ $index }})" class="text-red-500 hover:text-red-700" title="Hapus Item">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-12 gap-x-4 gap-y-2">
                                            <div class="sm:col-span-3">
                                                <label class="block text-xs font-medium text-gray-500">Kuantitas</label>
                                                <input type="number" wire:model.live.debounce.300ms="items.{{$index}}.quantity" class="w-full text-sm">
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="block text-xs font-medium text-gray-500">Isi per Boks</label>
                                                <input type="number" wire:model.live.debounce.300ms="items.{{$index}}.items_per_box" class="w-full text-sm">
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="block text-xs font-medium text-gray-500">Harga Satuan</label>
                                                <input type="number" step="any" wire:model.live.debounce.300ms="items.{{$index}}.cost" class="w-full text-sm" :disabled="$wire.items[{{$index}}]['purchase_by_box']">
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="block text-xs font-medium text-gray-500">Harga Boks</label>
                                                <input type="number" step="any" wire:model.live.debounce.300ms="items.{{$index}}.box_cost" class="w-full text-sm" :disabled="!$wire.items[{{$index}}]['purchase_by_box']">
                                            </div>
                                            <div class="sm:col-span-full flex items-center justify-between mt-2">
                                                <div class="flex items-center">
                                                    <input type="checkbox" wire:model.live="items.{{$index}}.purchase_by_box" class="h-4 w-4 rounded text-indigo-600 focus:ring-indigo-500">
                                                    <label class="ml-2 block text-sm text-gray-900">Beli per Boks?</label>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-xs text-gray-500">Subtotal</p>
                                                    <p class="text-sm font-semibold text-gray-800">Rp {{ number_format($item['total_cost'], 0, ',', '.') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Product -->
                <div class="relative mt-4">
                    <input type="text" wire:model.live.debounce.300ms="product_search" placeholder="Cari & tambah produk..." class="w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @if(count($search_results) > 0)
                        <div class="absolute z-10 mt-1 w-full md:w-1/2 bg-white border rounded-md shadow-lg">
                            @foreach($search_results as $product)
                                <div wire:click="addProduct({{ $product->id }})" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                                    {{ $product->name }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer & Total -->
            <div class="mt-8 border-t pt-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
                        <textarea id="notes" wire:model="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Total Nilai Pesanan</p>
                        <p class="text-3xl font-bold text-gray-900">Rp {{ number_format($total_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    @if($status !== 'received')
                    <button wire:click="save" class="px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg shadow-md hover:bg-gray-700">
                        Simpan Perubahan
                    </button>
                    @endif

                    @if($status === 'ordered' || $status === 'partially_received')
                    <button wire:click="receiveStock" wire:confirm="Anda yakin ingin memproses penerimaan barang? Stok dan harga modal akan diperbarui sesuai input Anda." class="px-6 py-3 bg-green-600 text-white font-bold rounded-lg shadow-md hover:bg-green-700">
                        Terima Barang & Update Stok
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

