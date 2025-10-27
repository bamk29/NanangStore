<div x-data="purchaseOrderManager(@js($items))" x-init="init()">
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
                        @if($orderId)
                        <option value="received">Received</option>
                        <option value="partially_received">Sebagian Diterima</option>
                        @endif
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- Product Search & Items Table -->
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900">Item Pesanan</h3>
                @error('items') <span class="text-red-500 text-sm mb-2 block">{{ $message }}</span> @enderror

                <div class="mt-4 flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <div class="space-y-4">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="bg-gray-50 p-4 rounded-lg border">
                                        <div class="flex items-start justify-between">
                                            <h4 class="font-semibold text-gray-800" x-text="item.product_name"></h4>
                                            <button @click.prevent="removeItem(index)" class="text-red-500 hover:text-red-700" title="Hapus Item">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-12 gap-x-4 gap-y-2">
                                            <div class="sm:col-span-3">
                                                <label class="block text-xs font-medium text-gray-500">Kuantitas</label>
                                                <input type="number" x-model.number="item.quantity" @input="updateItem(index)" class="w-full text-sm rounded-md border-gray-300">
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="block text-xs font-medium text-gray-500">Isi per Boks</label>
                                                <input type="number" x-model.number="item.items_per_box" @input="updateItem(index, 'items_per_box')" class="w-full text-sm rounded-md border-gray-300">
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="block text-xs font-medium text-gray-500">Harga Satuan</label>
                                                <input type="number" step="any" x-model.number="item.cost" @input="updateItem(index, 'cost')" class="w-full text-sm rounded-md border-gray-300" :disabled="item.purchase_by_box">
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="block text-xs font-medium text-gray-500">Harga Boks</label>
                                                <input type="number" step="any" x-model.number="item.box_cost" @input="updateItem(index, 'box_cost')" class="w-full text-sm rounded-md border-gray-300" :disabled="!item.purchase_by_box">
                                            </div>
                                            <div class="sm:col-span-full flex items-center justify-between mt-2">
                                                <div class="flex items-center">
                                                    <input type="checkbox" x-model="item.purchase_by_box" @change="updateItem(index)" class="h-4 w-4 rounded text-indigo-600 focus:ring-indigo-500">
                                                    <label class="ml-2 block text-sm text-gray-900">Beli per Boks?</label>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-xs text-gray-500">Subtotal</p>
                                                    <p class="text-sm font-semibold text-gray-800" x-text="formatCurrency(item.total_cost)"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Product -->
                <div class="relative mt-4">
                    <input type="text" x-model.debounce.300ms="productSearch" @input="searchProducts" placeholder="Cari & tambah produk..." class="w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <div x-show="isSearching" class="absolute z-10 mt-1 w-full md:w-1/2 bg-white px-4 py-2 text-sm text-gray-500">Mencari...</div>
                    <div x-show="searchResults.length > 0" @click.away="searchResults = []" class="absolute z-10 mt-1 w-full md:w-1/2 bg-white border rounded-md shadow-lg max-h-60 overflow-y-auto">
                        <template x-for="product in searchResults" :key="product.id">
                            <div @click="addProduct(product)" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                                <p x-text="product.name"></p>
                                <p class="text-xs text-gray-500" x-text="'Stok: ' + product.stock"></p>
                            </div>
                        </template>
                    </div>
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
                        <p class="text-3xl font-bold text-gray-900" x-text="formatCurrency(total_amount)"></p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="saveOrder" class="px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg shadow-md hover:bg-gray-700">
                        Simpan Perubahan
                    </button>

                    @if($status === 'ordered' || $status === 'partially_received')
                    <button wire:click="receiveStock" wire:confirm="Anda yakin ingin memproses penerimaan barang? Stok dan harga modal akan diperbarui sesuai input Anda." class="px-6 py-3 bg-green-600 text-white font-bold rounded-lg shadow-md hover:bg-green-700">
                        Terima Barang & Update Stok
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function purchaseOrderManager(initialItems) {
            return {
                items: initialItems,
                total_amount: 0,
                productSearch: '',
                searchResults: [],
                isSearching: false,

                init() {
                    this.calculateGrandTotal();
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
                },

                searchProducts() {
                    if (this.productSearch.length < 2) {
                        this.searchResults = [];
                        return;
                    }
                    this.isSearching = true;
                    const existingIds = this.items.map(i => i.product_id);

                    fetch(`/api/purchase-order-products?q=${this.productSearch}`)
                        .then(response => response.json())
                        .then(data => {
                            this.searchResults = data.filter(p => !existingIds.includes(p.id));
                            this.isSearching = false;
                        });
                },

                addProduct(product) {
                    const itemsPerBox = product.units_in_box > 0 ? product.units_in_box : 1;
                    const unitCost = product.unit_cost || 0;
                    const boxCost = product.box_cost || 0;

                    let finalUnitCost = 0;
                    let finalBoxCost = 0;

                    if (boxCost > 0) {
                        finalBoxCost = boxCost;
                        finalUnitCost = Math.round(boxCost / itemsPerBox);
                    } else if (unitCost > 0) {
                        finalUnitCost = unitCost;
                        finalBoxCost = unitCost * itemsPerBox;
                    }

                    this.items.push({
                        id: null,
                        product_id: product.id,
                        product_name: product.name,
                        purchase_by_box: true,
                        quantity: 1,
                        items_per_box: itemsPerBox,
                        box_cost: finalBoxCost,
                        cost: finalUnitCost,
                        unit_cost: finalUnitCost,
                        total_cost: finalBoxCost,
                        received_quantity: 0,
                        quantity_to_receive: 0,
                    });

                    this.productSearch = '';
                    this.searchResults = [];
                    this.calculateGrandTotal();
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                    this.calculateGrandTotal();
                },

                updateItem(index, field) {
                    let item = this.items[index];
                    const items_per_box = item.items_per_box > 0 ? item.items_per_box : 1;

                    if (field === 'box_cost') {
                        item.cost = Math.round(item.box_cost / items_per_box);
                    } else if (field === 'cost') {
                        item.box_cost = item.cost * items_per_box;
                    } else if (field === 'items_per_box') {
                        if (item.purchase_by_box) {
                            item.cost = Math.round(item.box_cost / items_per_box);
                        } else {
                            item.box_cost = item.cost * items_per_box;
                        }
                    }
                    
                    if (item.purchase_by_box) {
                        item.total_cost = item.quantity * item.box_cost;
                    } else {
                        item.total_cost = item.quantity * item.cost;
                    }

                    this.calculateGrandTotal();
                },

                calculateGrandTotal() {
                    this.total_amount = this.items.reduce((total, item) => total + item.total_cost, 0);
                },

                async saveOrder() {
                    // Frontend Validation
                    let errors = [];
                    if (!this.$wire.get('supplier_id')) {
                        errors.push('Supplier harus dipilih.');
                    }
                    if (!this.$wire.get('order_date')) {
                        errors.push('Tanggal Order wajib diisi.');
                    }
                    if (this.items.length === 0) {
                        errors.push('Harus ada minimal 1 item dalam pesanan.');
                    }

                    if (errors.length > 0) {
                        const errorHtml = '<ul class="list-disc list-inside text-left">' + 
                                            errors.map(e => `<li>${e}</li>`).join('') + 
                                        '</ul>';
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi Gagal!',
                            html: errorHtml,
                            confirmButtonColor: '#3b82f6'
                        });
                        return; // Stop execution
                    }

                    // Sync local Alpine data back to Livewire before saving
                    this.$wire.set('items', this.items);
                    this.$wire.set('total_amount', this.total_amount);
                    
                    // Give Livewire a moment to update, then call save
                    setTimeout(() => {
                        this.$wire.save();
                    }, 50);
                }
            }
        }
    </script>
</div>