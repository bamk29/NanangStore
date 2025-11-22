<div>
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $is_finalized ? 'Detail Retur Barang' : 'Buat Retur Barang' }}
                </h1>
                <p class="mt-2 text-sm text-gray-700">
                    {{ $is_finalized ? 'Detail barang yang telah diretur.' : 'Formulir untuk mencatat pengembalian barang ke supplier.' }}
                </p>
                @if($is_finalized)
                    <p class="mt-2 text-sm font-semibold text-gray-800">Nomor: {{ $return_number }}</p>
                @endif
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <a href="{{ route('goods-returns.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                    Kembali ke Daftar
                </a>
            </div>
        </div>

        <div class="mt-8">
            <div class="space-y-8">
                <!-- Header Form -->
                <div class="p-6 bg-white rounded-lg shadow">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="return_date" class="block text-sm font-medium text-gray-700">Tanggal Retur</label>
                            <input type="date" wire:model="return_date" id="return_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" @if($is_finalized) disabled @endif>
                            @error('return_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier</label>
                            <select wire:model="supplier_id" id="supplier_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" @if($is_finalized) disabled @endif>
                                <option value="">Pilih Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mt-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
                        <textarea wire:model="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" @if($is_finalized) disabled @endif></textarea>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="p-6 bg-white rounded-lg shadow">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Item Barang</h3>
                    
                    @if(!$is_finalized)
                    <div class="mt-4 relative">
                        <label for="product_search" class="sr-only">Cari Produk</label>
                        <input type="text" wire:model.debounce.300ms="product_search" id="product_search" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="Ketik untuk mencari produk...">
                        @if(count($search_results) > 0)
                            <ul class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg max-h-60 overflow-auto">
                                @foreach($search_results as $product)
                                    <li wire:click="addProduct({{ $product->id }})" class="px-4 py-2 cursor-pointer hover:bg-gray-100">{{ $product->name }} ({{ $product->code }})</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    @endif

                    <div class="mt-6 -mx-4 overflow-x-auto sm:-mx-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 w-2/5">Produk</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Jumlah Retur</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Harga Modal Satuan</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Subtotal</th>
                                    @if(!$is_finalized)
                                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Hapus</span></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($items as $index => $item)
                                    <tr wire:key="item-{{ $index }}">
                                        <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $item['product_name'] }}</td>
                                        <td class="px-3 py-4 text-sm">
                                            <input type="number" wire:model="items.{{ $index }}.quantity" class="w-24 rounded-md border-gray-300 shadow-sm" @if($is_finalized) disabled @endif>
                                        </td>
                                        <td class="px-3 py-4 text-sm">
                                            <input type="number" wire:model="items.{{ $index }}.cost" class="w-32 rounded-md border-gray-300 shadow-sm" @if($is_finalized) disabled @endif>
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-700">Rp {{ number_format($item['total_cost'], 0, ',', '.') }}</td>
                                        @if(!$is_finalized)
                                        <td class="py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <button wire:click="removeItem({{ $index }})" class="text-red-600 hover:text-red-800">&times; Hapus</button>
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $is_finalized ? 4 : 5 }}" class="px-6 py-8 text-center text-sm text-gray-500">
                                            Belum ada item yang ditambahkan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="{{ $is_finalized ? 3 : 4 }}" class="py-3 pl-4 pr-3 text-right text-sm font-semibold text-gray-900 sm:pl-6">Total Keseluruhan</td>
                                    <td class="px-3 py-3 text-left text-sm font-semibold text-gray-900">Rp {{ number_format($total_amount, 0, ',', '.') }}</td>
                                    @if(!$is_finalized)
                                    <td></td>
                                    @endif
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @error('items') <p class="mt-2 text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>

                <!-- Actions -->
                @if(!$is_finalized)
                <div class="flex justify-end">
                    <button wire:click="saveReturn" wire:loading.attr="disabled" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700">
                        <span wire:loading.remove>Simpan Retur</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
