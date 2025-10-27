<div x-data="cartManager()"
     x-init="loadCart(@js($initialItems), @js($initialCustomer), @js($initialType), @js($initialPendingId))"
     x-on:add-to-cart.window="addToCart($event.detail.product, $event.detail.quantity)"
     x-on:customer:selected.window="setCustomer($event.detail.customer)"
     x-on:customer:cleared.window="clearCustomer()"
     x-on:transaction-saved.window="window.location.href = '/pos/invoice/' + $event.detail.id"
     x-on:cart:reset.window="resetCart()"
     x-on:customer-selected-in-modal.window="showCustomerWarningModal = false"
     class="flex flex-col h-full bg-white">

    <!-- Tombol Eceran/Grosir -->
    <div class="p-2 bg-gray-100 flex-shrink-0">
        <div class="grid grid-cols-2 gap-1 bg-gray-200 p-1 rounded-lg">
            <button @click="transaction_type = 'retail'; recalculate()"
                :class="{ 'bg-white shadow': transaction_type === 'retail' }"
                class="px-4 py-2 text-sm font-bold rounded-md focus:outline-none">Eceran</button>
            <button @click="transaction_type = 'wholesale'; recalculate()"
                :class="{ 'bg-white shadow': transaction_type === 'wholesale' }"
                class="px-4 py-2 text-sm font-bold rounded-md focus:outline-none">Grosir</button>
        </div>
    </div>

    <!-- Daftar Item Keranjang (Scrolling) -->
    <div class="flex-grow overflow-y-auto min-h-0">
        <template x-if="items.length === 0">
            <div class="flex items-center justify-center h-full">
                <p class="text-gray-500">Keranjang belanja kosong</p>
            </div>
        </template>
        <div class="divide-y">
            <template x-for="item in items" :key="item.id">
                <div class="p-2 flex items-center space-x-1 my-4">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-gray-800 truncate" x-text="item.name"></p>
                        <p class="text-xs text-gray-500" x-text="item.price"></p>
                    </div>
                    <div class="flex items-center">
                        <button @click="decrement(item.id)"
                            class="w-7 h-7 flex items-center justify-center mx-1 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 active:scale-95 transition-all duration-150 focus:outline-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"></path></svg>
                        </button>
                        <div class="relative w-auto mx-1">

                            <input type="text" inputmode="decimal" x-model.lazy="item.quantity"
                                @change="validateAndRecalculate(item.id)"
                                class="w-16 h-7 text-center  bg-transparent text-sm font-semibold text-gray-800 focus:outline-none focus:ring-0">
                        </div>
                        <button @click="increment(item.id)"
                            class="w-7 h-7 flex items-center justify-center mx-1 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 active:scale-95 transition-all duration-150 focus:outline-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                    </div>
                    <div class="w-20 text-right font-semibold text-sm" x-text="formatCurrency(item.subtotal)"></div>
                    <button @click="remove(item.id)" class="flex-shrink-0 text-red-500 hover:text-red-700 p-1"><svg
                            class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg></button>
                </div>
            </template>
        </div>
    </div>

    <!-- Unified Footer -->
    <div class="flex-shrink-0 border-t">
        <!-- Totals -->
        <div class="p-3 bg-gray-50 space-y-3">
            <div class="flex justify-between items-center font-semibold"><span class="text-gray-600">Total Item</span><span class="text-lg" x-text="items.length + ' item'"></span></div>

            <template x-if="customer && customer.debt > 0">
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-3 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-bold">Pelanggan ini memiliki hutang</p>
                            <p class="text-sm" x-text="formatCurrency(customer.debt)"></p>
                        </div>
                        <label class="flex items-center cursor-pointer">
                            <span class="text-sm font-medium mr-2">Bayar Sekarang</span>
                            <input type="checkbox" x-model="include_old_debt" @change="calculateFinalTotal()"
                                class="h-5 w-5 rounded text-blue-600 focus:ring-blue-500">
                        </label>
                    </div>
                </div>
            </template>

            <div class="flex justify-between items-center font-bold text-2xl text-blue-600 border-t pt-3 mt-3">
                <span>Total</span><span x-text="formatCurrency(final_total)"></span>
            </div>
        </div>

        <!-- Customer Search -->
        <div class="p-4 bg-white shadow-inner border-t">
            @if ($selected_customer_name)
                <div class="flex items-center justify-between bg-blue-100 border border-blue-200 rounded-lg p-2">
                    <div>
                        <span class="text-xs text-gray-500">Pelanggan</span>
                        <p class="font-semibold text-blue-700">{{ $selected_customer_name }}</p>
                    </div>
                    <button wire:click="clearCustomer"
                        class="p-1 text-red-500 hover:text-red-700 rounded-full hover:bg-red-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            @else
                <div x-data="customerSearch()" class="relative">
                    <input type="text" x-model.debounce.300ms="searchQuery" @focus="handleFocus()"
                        @click.away="isOpen = false" placeholder="Cari pelanggan (nama/telp)..."
                        class="w-full pl-4 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">

                    <div x-show="isOpen && (results.length > 0 || isLoading)" x-transition
                        class="absolute z-[9999] w-full bottom-full mb-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <template x-if="isLoading">
                            <div class="px-4 py-2 text-gray-500">Mencari...</div>
                        </template>
                        <template x-for="customer in results" :key="customer.id">
                            <div @click="selectCustomer(customer)" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                                <p class="font-semibold" x-text="customer.name"></p>
                                <p class="text-sm text-gray-600" x-text="customer.phone"></p>
                            </div>
                        </template>
                        <template x-if="!isLoading && results.length === 0 && searchQuery.length > 0">
                            <div class="px-4 py-2 text-gray-500">Pelanggan tidak ditemukan.</div>
                        </template>

                        <div @click="$wire.set('showCustomerCreateModal', true)"
                            class="px-4 py-3 text-center text-blue-600 font-semibold cursor-pointer border-t hover:bg-gray-50 rounded-b-lg">
                            + Buat Pelanggan Baru
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Bayar Button -->
        <div class="p-3 bg-gray-50 border-t">
            <button @click="initiatePayment()" :disabled="items.length === 0"
                class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 disabled:bg-gray-300">
                Bayar
            </button>
        </div>
    </div>

    <!-- Modals -->
    <!-- Modal Pilih Pelanggan -->
    <div x-show="showCustomerWarningModal" x-cloak
        class="fixed inset-0 z-50 flex items-start sm:items-center justify-center bg-black/60 backdrop-blur-sm p-4 pt-20"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div @click.away="showCustomerWarningModal = false"
            class="bg-white rounded-2xl shadow-xl w-full max-w-md transform transition-all"
            x-show="showCustomerWarningModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Pilih atau Buat Pelanggan</h3>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded-r-lg mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.21 3.001-1.742 3.001H4.42c-1.532 0-2.492-1.667-1.742-3.001l5.58-9.92zM10 13a1 1 0 110-2 1 1 0 010 2zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">Transaksi tanpa pelanggan tidak akan tercatat dalam riwayat hutang atau poin loyalitas.</p>
                        </div>
                    </div>
                </div>
                <div x-data="customerSearch()" class="relative">
                    <input type="text" x-model.debounce.300ms="searchQuery" @focus="handleFocus()"
                        @keydown.escape="isOpen = false" placeholder="Cari pelanggan (nama/telp)..."
                        class="w-full pl-4 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div x-show="isOpen" x-transition
                        class="absolute z-10 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <template x-if="isLoading"><div class="px-4 py-2 text-gray-500">Mencari...</div></template>
                        <template x-for="customer in results" :key="customer.id">
                            <div @click="selectCustomer(customer)" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                                <p class="font-semibold" x-text="customer.name"></p>
                                <p class="text-sm text-gray-600" x-text="customer.phone"></p>
                            </div>
                        </template>
                        <template x-if="!isLoading && results.length === 0 && searchQuery.length > 0">
                            <div class="px-4 py-2 text-gray-500">Pelanggan tidak ditemukan.</div>
                        </template>
                        <div @click="$wire.set('showCustomerCreateModal', true); showCustomerWarningModal = false"
                            class="px-4 py-3 text-center text-blue-600 font-semibold cursor-pointer border-t hover:bg-gray-50 rounded-b-lg">
                            + Buat Pelanggan Baru
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-6 pb-4 space-y-2 border-t bg-gray-50 rounded-b-2xl pt-4">
                <button @click="showCustomerWarningModal = false; showPaymentModal = true"
                    class="w-full px-4 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none">
                    Lanjutkan (Tanpa Pelanggan)
                </button>
                <button @click="showCustomerWarningModal = false"
                    class="w-full px-4 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 focus:outline-none">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Pembayaran -->
    <div x-show="showPaymentModal" x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm overflow-hidden"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div @click.away="showPaymentModal = false"
            class="relative bg-white w-full max-w-2xl flex flex-col max-h-[90vh] transform transition-all duration-300 ease-out rounded-t-2xl sm:rounded-2xl sm:mb-0 mb-24"
            x-show="showPaymentModal" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="translate-y-0 sm:scale-100"
            x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95">
            <div class="p-4 md:p-5 border-b bg-gray-50 flex justify-between items-center">
                <h3 class="text-lg md:text-xl font-bold text-gray-800">💰 Detail Pembayaran</h3>
                <button @click="showPaymentModal = false" class="text-gray-500 hover:text-gray-800 text-lg font-bold">&times;</button>
            </div>
            <div class="p-4 md:p-6 space-y-5 overflow-y-auto flex-1">
                <div class="text-center">
                    <p class="text-gray-500 text-sm md:text-base">Total Tagihan</p>
                    <p class="text-3xl md:text-4xl font-bold text-gray-900" x-text="formatCurrency(final_total)"></p>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="method in ['cash', 'transfer', 'debt']" :key="method">
                            <label class="p-3 border rounded-lg text-center cursor-pointer select-none transition"
                                :class="{ 'bg-blue-600 text-white border-blue-600': payment_method === method, 'bg-gray-100 hover:bg-gray-200 text-gray-700': payment_method !== method }">
                                <input type="radio" class="hidden" x-model="payment_method" :value="method">
                                <span x-text="method === 'cash' ? 'Tunai' : method === 'transfer' ? 'Transfer' : 'Hutang'"></span>
                            </label>
                        </template>
                    </div>
                </div>
                <template x-if="payment_method !== 'debt'">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Uang Dibayarkan</label>
                        <input type="text" x-model="paid_amount_display" @input="formatPaidAmount($event)" placeholder="0" class="w-full border rounded-lg p-2 font-bold text-lg text-right focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                        <div class="mt-3 grid grid-cols-4 gap-2 text-sm">
                            <template x-for="val in [10000, 20000, 50000, 100000]" :key="val">
                                <button class="px-2 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 font-semibold" @click="addPaidAmount(val)" x-text="val.toLocaleString('id-ID')"></button>
                            </template>
                            <button class="px-2 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 col-span-2 font-semibold" @click="setPaidAmount(final_total)">Uang Pas</button>
                            <button class="px-2 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 col-span-2 font-semibold" @click="resetAmount()">Reset</button>
                        </div>
                        <template x-if="paid_amount < final_total && customer">
                            <div class="text-orange-700 bg-orange-50 border-l-4 border-orange-500 p-2 text-xs mt-3 rounded">
                                Kekurangan <strong x-text="formatCurrency(final_total - paid_amount)"></strong> akan otomatis ditambahkan sebagai hutang pelanggan.
                            </div>
                        </template>
                        <template x-if="underpaymentError">
                            <div class="text-red-500 text-sm mt-2">Uang kurang & tidak ada pelanggan terpilih untuk mencatat hutang!</div>
                        </template>
                    </div>
                </template>
                <div>
                    <label class="block text-sm font-semibold mb-2">Kembalian</label>
                    <div class="text-xl md:text-2xl font-bold" :class="{ 'text-red-600': change < 0, 'text-green-600': change >= 0 }" x-text="formatCurrency(change)"></div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Catatan</label>
                    <textarea x-model="notes" rows="2" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none" placeholder="Catatan tambahan..."></textarea>
                </div>
            </div>
            <div class="p-4 md:p-5 border-t bg-gray-50 flex flex-col md:flex-row gap-2 md:justify-end">
                <button @click="showPaymentModal=false" class="px-4 py-2 rounded-lg text-gray-700 bg-gray-200 hover:bg-gray-300 w-full md:w-auto">Batal</button>
                <button @click="holdSale()" class="px-4 py-2 rounded-lg text-white bg-yellow-500 hover:bg-yellow-600 w-full md:w-auto">Simpan & Tunda</button>
                <button @click="completePayment()" :disabled="(payment_method === 'debt' && !customer) || isProcessingPayment" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-wait w-full md:w-auto flex items-center justify-center">
                    <template x-if="isProcessingPayment">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>Memproses...</span>
                    </template>
                    <template x-if="!isProcessingPayment"><span>Selesaikan Pembayaran</span></template>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Buat Pelanggan (dikontrol oleh Livewire) --}}
    @if ($showCustomerCreateModal)
        <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="$wire.set('showCustomerCreateModal', false)">
                <h3 class="text-lg font-bold mb-4">Buat Pelanggan Baru</h3>
                <div class="space-y-4">
                    <div><label for="new_name">Nama Pelanggan</label><input id="new_name" type="text" wire:model="new_customer_name" class="w-full border rounded-lg p-2 mt-1">@error('new_customer_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror</div>
                    <div><label for="new_phone">No. Telepon (Opsional)</label><input id="new_phone" type="text" wire:model="new_customer_phone" class="w-full border rounded-lg p-2 mt-1">@error('new_customer_phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror</div>
                </div>
                <div class="mt-6 flex justify-end space-x-4">
                    <button @click="$wire.set('showCustomerCreateModal', false)" class="px-4 py-2 rounded-lg text-gray-600 bg-gray-100 hover:bg-gray-200">Batal</button>
                    <button wire:click="createNewCustomer" class="px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600">Simpan</button>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    function customerSearch() {
        return {
            searchQuery: '',
            results: [],
            isOpen: false,
            isLoading: false,
            init() {
                this.$watch('searchQuery', (value) => {
                    this.fetchCustomers();
                });
            },
            fetchCustomers() {
                this.isLoading = true;
                fetch(`/api/customers?q=${this.searchQuery}`)
                    .then(response => response.json())
                    .then(data => {
                        this.results = data;
                        this.isLoading = false;
                        this.isOpen = true;
                    });
            },
            handleFocus() {
                if (this.searchQuery === '') {
                    this.fetchCustomers();
                }
                this.isOpen = true;
            },
            selectCustomer(customer) {
                this.$wire.selectCustomer(customer.id, customer.name);
                this.searchQuery = '';
                this.results = [];
                this.isOpen = false;
                this.$dispatch('customer-selected-in-modal');
            }
        }
    }

    function cartManager() {
        return {
            items: [],
            subtotal: 0,
            final_total: 0,
            transaction_type: 'retail',
            customer: null,
            include_old_debt: false,
            showPaymentModal: false,
            showCustomerWarningModal: false,
            payment_method: 'cash',
            paid_amount: 0,
            paid_amount_display: '0',
            notes: '',
            change: 0,
            underpaymentError: false,
            pending_transaction_id: null, // Lacak ID transaksi pending
            isProcessingPayment: false,

            init() {
                this.$watch('paid_amount', () => this.calculateChange());
                this.$watch('final_total', () => this.calculateChange());
                this.$watch('include_old_debt', () => this.calculateFinalTotal());
                this.$watch('showPaymentModal', (value) => {
                    if (!value) {
                        // Reset state when modal is closed
                        this.isProcessingPayment = false;
                    }
                });
            },

            initiatePayment() {
                if (!this.customer) {
                    this.showCustomerWarningModal = true;
                } else {
                    this.showPaymentModal = true;
                }
            },

            addToCart(product, quantity = 1) {
                let existingItem = this.items.find(i => i.id === product.id);
                const newQuantity = parseFloat(quantity) || 1;

                if (existingItem) {
                    const totalQuantity = existingItem.quantity + newQuantity;
                    if (totalQuantity <= product.stock) {
                        existingItem.quantity = totalQuantity;
                    } else {
                        existingItem.quantity = product.stock; // Set to max stock
                        window.Livewire.dispatch('show-alert', {
                            type: 'error',
                            message: 'Stok tidak cukup. Kuantitas diatur ke stok maksimal.'
                        });
                    }
                } else {
                    if (newQuantity <= product.stock) {
                        this.items.push({
                            id: product.id,
                            name: product.name,
                            code: product.code,
                            stock: product.stock,
                            retail_price: product.retail_price,
                            wholesale_price: product.wholesale_price,
                            wholesale_min_qty: product.wholesale_min_qty,
                            quantity: newQuantity,
                            price: product.retail_price,
                            subtotal: product.retail_price * newQuantity
                        });
                    } else {
                        window.Livewire.dispatch('show-alert', {
                            type: 'error',
                            message: 'Stok tidak cukup.'
                        });
                    }
                }
                this.recalculate();
            },

            increment(id) {
                let item = this.items.find(i => i.id === id);
                if (item && item.quantity < item.stock) {
                    item.quantity = parseFloat(String(item.quantity).replace(',', '.')) + 1;
                    this.recalculate();
                }
            },
            decrement(id) {
                let item = this.items.find(i => i.id === id);
                if (item && item.quantity > 1) {
                    item.quantity = parseFloat(String(item.quantity).replace(',', '.')) - 1;
                    this.recalculate();
                } else if (item) {
                    this.remove(id);
                }
            },
            remove(id) {
                this.items = this.items.filter(i => i.id !== id);
                this.recalculate();
            },

            validateAndRecalculate(id) {
                let item = this.items.find(i => i.id === id);
                if (item) {
                    let qty = parseFloat(String(item.quantity).replace(',', '.'));
                    if (isNaN(qty) || qty <= 0) {
                        this.remove(id);
                        return;
                    }
                    if (qty > item.stock) {
                        item.quantity = item.stock;
                        window.Livewire.dispatch('show-alert', {
                            type: 'error',
                            message: 'Stok tidak cukup'
                        });
                    } else {
                        item.quantity = qty;
                    }
                    this.recalculate();
                }
            },

            recalculate() {
                let currentSubtotal = 0;
                this.items.forEach(item => {
                    let useWholesale = this.transaction_type === 'wholesale' && item.quantity >= item
                        .wholesale_min_qty;
                    item.price = useWholesale ? item.wholesale_price : item.retail_price;
                    item.subtotal = item.price * item.quantity;
                    currentSubtotal += item.subtotal;
                });
                this.subtotal = currentSubtotal;
                this.calculateFinalTotal();
                this.$dispatch('cart-updated', {
                    items: this.items
                });
            },

            setCustomer(customer) {
                this.customer = customer;
                this.calculateFinalTotal();
            },
            clearCustomer() {
                this.customer = null;
                this.include_old_debt = false;
                this.calculateFinalTotal();
            },

            calculateFinalTotal() {
                let total = this.subtotal;
                if (this.include_old_debt && this.customer && this.customer.debt > 0) {
                    total += parseFloat(this.customer.debt);
                }
                this.final_total = total;
            },

            formatPaidAmount(event) {
                let value = event.target.value.replace(/[^\d]/g, '');
                this.paid_amount = Number(value);
                this.paid_amount_display = value === '' ? '0' : Number(value).toLocaleString('id-ID');
            },

            addPaidAmount(amount) {
                this.paid_amount += amount;
                this.paid_amount_display = this.paid_amount.toLocaleString('id-ID');
            },

            setPaidAmount(amount) {
                this.paid_amount = amount;
                this.paid_amount_display = amount.toLocaleString('id-ID');
            },

            resetAmount() {
                this.paid_amount = 0;
                this.paid_amount_display = '0';
            },

            calculateChange() {
                if (this.payment_method === 'debt') {
                    this.change = 0;
                    return;
                }
                this.change = this.paid_amount - this.final_total;
            },

            completePayment() {
                if (this.isProcessingPayment) return;

                // Hanya error jika uang kurang DAN tidak ada pelanggan valid yang dipilih
                if (this.payment_method !== 'debt' && this.paid_amount < this.final_total && (!this.customer || !this
                        .customer.id)) {
                    this.underpaymentError = true;
                    setTimeout(() => this.underpaymentError = false, 3000);
                    return;
                }
                this.underpaymentError = false;
                this.isProcessingPayment = true;

                let paymentDetails = {
                    customer: this.customer,
                    subtotal: this.subtotal,
                    final_total: this.final_total,
                    include_old_debt: this.include_old_debt,
                    payment_method: this.payment_method,
                    paid_amount: this.paid_amount, // Kirim jumlah bayar aktual
                    change: this.change > 0 ? this.change : 0,
                    notes: this.notes,
                    transaction_type: this.transaction_type,
                    pending_id: this.pending_transaction_id // Kirim ID kembali ke backend
                };
                this.$wire.processPaymentFinal(this.items, paymentDetails);
            },

            holdSale() {
                let paymentDetails = {
                    customer: this.customer,
                    subtotal: this.subtotal,
                    transaction_type: this.transaction_type,
                    notes: this.notes,
                };
                this.$wire.holdTransaction(this.items, paymentDetails);
                this.showPaymentModal = false;
            },

            resetCart() {
                this.items = [];
                this.recalculate();
                this.clearCustomer();
                this.notes = '';
                this.showPaymentModal = false;
                this.pending_transaction_id = null; // Reset ID saat keranjang dibersihkan
            },

            loadCart(items, customer, type, pending_id = null) { // Terima pending_id

                this.items = items.map(item => {
                    // parseFloat akan menghilangkan angka nol yang tidak perlu di belakang koma
                    // Contoh: "1.500" menjadi 1.5, "2.00" menjadi 2
                    item.quantity = parseFloat(item.quantity);
                    return item;
                });
                this.transaction_type = type;
                if (customer) {
                    this.setCustomer(customer);
                }
                this.pending_transaction_id = pending_id; // Simpan ID
                this.recalculate();
            },
            formatCurrency(amount) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(amount);
            }
        }
    }
</script>
