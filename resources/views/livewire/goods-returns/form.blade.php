<div x-data="goodsReturnForm()">
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
                <a href="{{ route('goods-returns.index') }}" wire:navigate class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
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
                    <div class="mt-4 relative" @click.away="showResults = false">
                        <label for="product_search" class="sr-only">Cari Produk</label>
                        <div class="relative">
                            <input type="text" id="product_search"
                                   x-model.debounce.300ms="searchQuery"
                                   @focus="showResults = true"
                                   class="w-full rounded-md border-gray-300 shadow-sm pl-10"
                                   placeholder="Ketik untuk mencari produk (min. 2 huruf)..."
                                   autocomplete="off">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        
                        <div x-show="showResults" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg max-h-60 overflow-auto">
                            <div x-show="isLoading" class="px-4 py-2 text-sm text-gray-500">Mencari...</div>
                            <ul x-show="!isLoading && searchResults.length > 0">
                                <template x-for="product in searchResults" :key="product.id">
                                    <li @click="selectProduct(product)" class="px-4 py-2 cursor-pointer hover:bg-gray-100" x-text="`${product.name} (${product.code})`"></li>
                                </template>
                            </ul>
                            <div x-show="!isLoading && searchQuery.length >= 2 && searchResults.length === 0" class="px-4 py-2 text-sm text-gray-500">
                                Produk tidak ditemukan.
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="mt-6 flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0 w-2/5">Produk</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Jumlah Retur</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Harga Modal Satuan</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Subtotal</th>
                                            @if(!$is_finalized)
                                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0"><span class="sr-only">Hapus</span></th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @forelse($items as $index => $item)
                                            <tr wire:key="item-{{ $index }}">
                                                <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">{{ $item['product_name'] }}</td>
                                                <td class="px-3 py-4 text-sm">
                                                    <input type="number" wire:model.lazy="items.{{ $index }}.quantity" class="w-24 rounded-md border-gray-300 shadow-sm" @if($is_finalized) disabled @endif>
                                                </td>
                                                <td class="px-3 py-4 text-sm">
                                                    <input type="number" wire:model.lazy="items.{{ $index }}.cost" class="w-32 rounded-md border-gray-300 shadow-sm" @if($is_finalized) disabled @endif>
                                                </td>
                                                <td class="px-3 py-4 text-sm text-gray-700">Rp {{ number_format($item['total_cost'], 0, ',', '.') }}</td>
                                                @if(!$is_finalized)
                                                <td class="py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
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
                                            <td colspan="{{ $is_finalized ? 3 : 4 }}" class="py-3 pl-4 pr-3 text-right text-sm font-semibold text-gray-900 sm:pl-0">Total Keseluruhan</td>
                                            <td class="px-3 py-3 text-left text-sm font-semibold text-gray-900">Rp {{ number_format($total_amount, 0, ',', '.') }}</td>
                                            @if(!$is_finalized)
                                            <td></td>
                                            @endif
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
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

    <script>
        function goodsReturnForm() {
            return {
                searchQuery: '',
                searchResults: [],
                isLoading: false,
                showResults: false,
                init() {
                    this.$watch('searchQuery', (value) => {
                        if (value.length < 2) {
                            this.searchResults = [];
                            this.showResults = false;
                            return;
                        }
                        this.isLoading = true;
                        this.showResults = true;
                        
                        fetch(`/api/products?q=${value}`)
                            .then(response => response.json())
                            .then(data => {
                                this.searchResults = data;
                            })
                            .catch(error => console.error('Error fetching products:', error))
                            .finally(() => this.isLoading = false);
                    });
                },
                selectProduct(product) {
                    this.$wire.addProduct(product);
                    this.searchQuery = '';
                    this.searchResults = [];
                    this.showResults = false;
                }
            }
        }
    </script>
</div>

