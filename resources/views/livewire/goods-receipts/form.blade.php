<div x-data="goodsReceiptForm(@js($items))" x-on:items-loaded.window="loadItems($event.detail)">
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $is_finalized ? 'Detail Penerimaan Barang' : 'Buat Penerimaan Barang' }}
                </h1>
                <p class="mt-2 text-sm text-gray-700">
                    {{ $is_finalized ? 'Detail barang yang telah diterima.' : 'Formulir untuk mencatat penerimaan barang dari supplier, baik dari PO atau manual.' }}
                </p>
                @if($is_finalized)
                    <p class="mt-2 text-sm font-semibold text-gray-800">Nomor: {{ $receipt_number }}</p>
                @endif
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <a href="{{ route('goods-receipts.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                    Kembali ke Daftar
                </a>
            </div>
        </div>

        <div class="mt-8">
            <div class="space-y-8">
                <!-- Header Form -->
                <div class="p-6 bg-white rounded-lg shadow">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="receipt_date" class="block text-sm font-medium text-gray-700">Tanggal Penerimaan</label>
                            <input type="date" wire:model="receipt_date" id="receipt_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" @if($is_finalized) disabled @endif>
                            @error('receipt_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier</label>
                            <select wire:model="supplier_id" id="supplier_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" @if($is_finalized || $purchase_order_id) disabled @endif>
                                <option value="">Pilih Supplier (Opsional)</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="purchase_order_id" class="block text-sm font-medium text-gray-700">Dari Purchase Order (PO)</label>
                            <select wire:model="purchase_order_id" id="purchase_order_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" @if($is_finalized) disabled @endif>
                                <option value="">Penerimaan Manual</option>
                                @foreach($purchaseOrders as $po)
                                    <option value="{{ $po->id }}">{{ $po->order_number }} - {{ $po->supplier->name }}</option>
                                @endforeach
                            </select>
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
                    
                    @if(!$is_finalized && !$purchase_order_id)
                    <div class="mt-4 relative">
                        <label for="product_search" class="sr-only">Cari Produk</label>
                        <input type="text" x-model.debounce.300ms="searchQuery" @keydown.enter.prevent="handleBarcodeScan()" x-ref="searchInput" id="product_search" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="Ketik untuk mencari produk atau scan barcode...">
                        <div x-show="isSearching" class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <div x-show="searchResults.length > 0" x-cloak class="absolute z-50 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg max-h-60 overflow-auto">
                            <ul>
                                <template x-for="(product, index) in searchResults" :key="product.id">
                                    <li @click="addProduct(product)" class="px-4 py-2 cursor-pointer hover:bg-gray-100" :class="{'bg-gray-100': index === selectedIndex }">
                                        <span x-text="product.name"></span>
                                        <span class="text-gray-500" x-text="'(' + product.code + ')'"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                    @endif

                    <div class="mt-6 -mx-4 overflow-x-auto sm:-mx-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 w-2/5">Produk</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Jumlah</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Harga Modal</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Harga Jual</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Subtotal</th>
                                    @if(!$is_finalized)
                                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Hapus</span></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6" x-text="item.product_name"></td>
                                        <td class="px-3 py-4 text-sm">
                                            <input type="number" x-model.number="item.quantity_received" @input="calculateTotals()" class="w-24 rounded-md border-gray-300 shadow-sm" @if($is_finalized) disabled @endif>
                                        </td>
                                        <td class="px-3 py-4 text-sm">
                                            <input type="number" x-model.number="item.cost" @input="calculateTotals()" class="w-32 rounded-md border-gray-300 shadow-sm" @if($is_finalized) disabled @endif>
                                        </td>
                                        <td class="px-3 py-4 text-sm">
                                            <input type="number" x-model.number="item.retail_price" class="w-32 rounded-md border-gray-300 shadow-sm" @if($is_finalized) disabled @endif>
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-700" x-text="formatCurrency(item.total_cost)"></td>
                                        @if(!$is_finalized)
                                        <td class="py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <button @click="removeItem(index)" class="text-red-600 hover:text-red-800">&times; Hapus</button>
                                        </td>
                                        @endif
                                    </tr>
                                </template>
                                <template x-if="items.length === 0">
                                    <tr>
                                        <td colspan="{{ $is_finalized ? 5 : 6 }}" class="px-6 py-8 text-center text-sm text-gray-500">
                                            Belum ada item yang ditambahkan.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="{{ $is_finalized ? 4 : 5 }}" class="py-3 pl-4 pr-3 text-right text-sm font-semibold text-gray-900 sm:pl-6">Total Keseluruhan</td>
                                    <td class="px-3 py-3 text-left text-sm font-semibold text-gray-900" x-text="formatCurrency(grandTotal)"></td>
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
                    <button @click="save()" wire:loading.attr="disabled" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700">
                        <span wire:loading.remove>Simpan Penerimaan</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
    <script>
        function goodsReceiptForm(initialItems) {
            return {
                items: [],
                grandTotal: 0,
                searchQuery: '',
                searchResults: [],
                isSearching: false,
                selectedIndex: -1,
                init() {
                    this.items = JSON.parse(JSON.stringify(initialItems)).map(item => ({
                        ...item,
                        retail_price: item.retail_price || 0
                    }));
                    this.calculateTotals();
                    
                    this.$watch('searchQuery', (query) => {
                        if (query.length < 2) {
                            this.searchResults = [];
                            return;
                        }
                        this.isSearching = true;
                        fetch(`/api/products?q=${query}`)
                            .then(res => res.json())
                            .then(data => {
                                this.searchResults = data;
                                this.isSearching = false;
                            });
                    });

                    Livewire.on('items-loaded', newItems => {
                        this.items = newItems.map(item => ({
                            ...item,
                            retail_price: item.retail_price || 0
                        }));
                        this.calculateTotals();
                    });
                },
                calculateTotals() {
                    let total = 0;
                    this.items.forEach(item => {
                        let qty = parseFloat(item.quantity_received) || 0;
                        let cost = parseFloat(item.cost) || 0;
                        item.total_cost = qty * cost;
                        total += item.total_cost;
                    });
                    this.grandTotal = total;
                },
                addProduct(product) {
                    if (this.items.find(item => item.product_id === product.id)) {
                        // Maybe flash a message
                        return;
                    }
                    this.items.push({
                        product_id: product.id,
                        product_name: product.name,
                        quantity_received: 1,
                        cost: product.cost_price || 0,
                        retail_price: product.retail_price || 0,
                        total_cost: product.cost_price || 0,
                    });
                    this.searchQuery = '';
                    this.searchResults = [];
                },
                handleBarcodeScan() {
                    if (this.searchQuery.length < 3) return;
                    this.isSearching = true;
                    fetch(`/api/products/by-code/${this.searchQuery}`)
                        .then(res => {
                            if (!res.ok) throw new Error('Produk tidak ditemukan');
                            return res.json();
                        })
                        .then(product => {
                            this.addProduct(product);
                        })
                        .catch(err => {
                            // Maybe show an error
                            console.error(err);
                        })
                        .finally(() => {
                            this.isSearching = false;
                        });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                save() {
                    this.$wire.set('items', this.items);
                    this.$wire.set('total_amount', this.grandTotal);
                    this.$wire.saveReceipt();
                },
                formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
                }
            }
        }
    </script>
</div>
