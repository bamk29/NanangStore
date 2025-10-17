<div>
    {{-- Bagian ini hanya untuk logika pencarian pelanggan via Livewire --}}
    <div class="p-4 border-t bg-white shadow-inner space-y-3 sticky bottom-0">

        @if ($selected_customer_name)
            <div class="flex items-center justify-between bg-blue-100 border border-blue-200 rounded-lg p-2">
                <div>
                    <span class="text-xs text-gray-500">Pelanggan</span>
                    <p class="font-semibold text-blue-700">{{ $selected_customer_name }}</p>
                </div>
                <button wire:click="clearCustomer"
                    class="p-1 text-red-500 hover:text-red-700 rounded-full hover:bg-red-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        @else
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="customer_search"
                    placeholder="Cari pelanggan (nama/telp)..."
                    class="w-full pl-4 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if (strlen($customer_search) >= 2)
                    <div
                        class="absolute z-20 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        @forelse($customers as $customer)
                            <div wire:click="selectCustomer({{ $customer->id }}, '{{ addslashes($customer->name) }}')"
                                class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                                <p class="font-semibold">{{ $customer->name }}</p>
                                <p class="text-sm text-gray-600">{{ $customer->phone }}</p>
                            </div>
                        @empty
                            <div class="px-4 py-2 text-gray-500">Pelanggan tidak ditemukan.</div>
                        @endforelse
                        <div wire:click="$set('showCustomerCreateModal', true)"
                            class="px-4 py-3 text-center text-blue-600 font-semibold cursor-pointer border-t hover:bg-gray-50 rounded-b-lg">
                            + Buat Pelanggan Baru
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Komponen utama keranjang dengan AlpineJS --}}
    <div x-data="cartManager()" x-init="loadCart(@js($initialItems), @js($initialCustomer), @js($initialType), @js($initialPendingId))" x-on:add-to-cart.window="addToCart($event.detail.product)"
        x-on:customer:selected.window="setCustomer($event.detail.customer)"
        x-on:customer:cleared.window="clearCustomer()"
        x-on:transaction-saved.window="window.location.href = '/pos/invoice/' + $event.detail.id"
        x-on:cart:reset.window="resetCart()"
        class="flex flex-col h-full bg-white">

        <!-- Tombol Eceran/Grosir -->
        <div class="p-2 bg-gray-100">
            <div class="grid grid-cols-2 gap-1 bg-gray-200 p-1 rounded-lg">
                <button @click="transaction_type = 'retail'; recalculate()"
                    :class="{ 'bg-white shadow': transaction_type === 'retail' }"
                    class="px-4 py-2 text-sm font-bold rounded-md focus:outline-none">Eceran</button>
                <button @click="transaction_type = 'wholesale'; recalculate()"
                    :class="{ 'bg-white shadow': transaction_type === 'wholesale' }"
                    class="px-4 py-2 text-sm font-bold rounded-md focus:outline-none">Grosir</button>
            </div>
        </div>

        <!-- Daftar Item Keranjang -->
        <div class="flex-grow overflow-y-auto">
            <template x-if="items.length === 0">
                <div class="flex items-center justify-center h-full">
                    <p class="text-gray-500">Keranjang belanja kosong</p>
                </div>
            </template>
            <div class="divide-y">
                <template x-for="item in items" :key="item.id">
                    <div class="p-3 flex items-center space-x-2">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-800" x-text="item.name"></p>
                            <p class="text-sm text-gray-600" x-text="formatCurrency(item.price)"></p>
                        </div>
                        <div class="flex items-center">
                            <button @click="decrement(item.id)"
                                class="w-8 h-8 flex items-center justify-center mx-2 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 active:scale-95 transition-all duration-150 focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M20 12H4"></path>
                                </svg>
                            </button>
                            <input type="text" inputmode="decimal" x-model.lazy="item.quantity"
                                @change="validateAndRecalculate(item.id)"
                                class="w-12 h-8 text-center border-none bg-transparent text-base font-semibold text-gray-800 focus:outline-none focus:ring-0">
                            <button @click="increment(item.id)"
                                class="w-8 h-8 flex items-center justify-center mx-2 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 active:scale-95 transition-all duration-150 focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="w-24 text-right font-semibold" x-text="formatCurrency(item.subtotal)"></div>
                        <button @click="remove(item.id)" class="text-red-500 hover:text-red-700 p-1"><svg
                                class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg></button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Footer Keranjang -->
        <div class="p-3 border-t bg-gray-50 space-y-3">
            <div class="flex justify-between items-center font-semibold"><span
                    class="text-gray-600">Subtotal</span><span class="text-lg" x-text="formatCurrency(subtotal)"></span>
            </div>

            <!-- Fitur Hutang Baru -->
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

            <button @click="showPaymentModal = true" :disabled="items.length === 0"
                class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 disabled:bg-gray-300">
                Bayar
            </button>
        </div>

        <!-- Modal Pembayaran -->
        <div x-show="showPaymentModal" x-cloak
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm overflow-hidden"
            x-transition:enter="ease-out duration-300" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            x-transition:leave="ease-in duration-200" 
            x-transition:leave-start="opacity-100" 
            x-transition:leave-end="opacity-0">
            <!-- Wrapper Modal -->
            <div @click.away="showPaymentModal = false"
                class="relative bg-white w-full max-w-2xl flex flex-col max-h-[90vh] transform transition-all duration-300 ease-out rounded-t-2xl sm:rounded-2xl sm:mb-0 mb-24"
                x-show="showPaymentModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="translate-y-0 sm:scale-100"
                x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95">
                <!-- Header -->
                <div class="p-4 md:p-5 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg md:text-xl font-bold text-gray-800">💰 Detail Pembayaran</h3>
                    <button @click="showPaymentModal = false"
                        class="text-gray-500 hover:text-gray-800 text-lg font-bold">&times;</button>
                </div>

                <!-- Isi Konten Modal -->
                <div class="p-4 md:p-6 space-y-5 overflow-y-auto flex-1">
                    <div class="text-center">
                        <p class="text-gray-500 text-sm md:text-base">Total Tagihan</p>
                        <p class="text-3xl md:text-4xl font-bold text-gray-900" x-text="formatCurrency(final_total)">
                        </p>
                    </div>

                    <!-- Metode Pembayaran -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Metode Pembayaran</label>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="method in ['cash', 'transfer', 'debt']" :key="method">
                                <label class="p-3 border rounded-lg text-center cursor-pointer select-none transition"
                                    :class="{
                                        'bg-blue-600 text-white border-blue-600': payment_method === method,
                                        'bg-gray-100 hover:bg-gray-200 text-gray-700': payment_method !== method
                                    }">
                                    <input type="radio" class="hidden" x-model="payment_method"
                                        :value="method">
                                    <span
                                        x-text="method === 'cash' ? 'Tunai' : method === 'transfer' ? 'Transfer' : 'Hutang'"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <!-- Input Pembayaran -->
                    <template x-if="payment_method !== 'debt'">
                        <div>
                            <label class="block text-sm font-semibold mb-2">Uang Dibayarkan</label>
                            <input type="text" x-model="paid_amount_display" @input="formatPaidAmount($event)"
                                placeholder="0"
                                class="w-full border rounded-lg p-2 font-bold text-lg text-right focus:ring-2 focus:ring-blue-500 focus:outline-none" />

                            <!-- Tombol Nominal Cepat -->
                            <div class="mt-3 grid grid-cols-4 gap-2 text-sm">
                                <template x-for="val in [10000, 20000, 50000, 100000]" :key="val">
                                    <button class="px-2 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 font-semibold"
                                        @click="addPaidAmount(val)" x-text="val.toLocaleString('id-ID')"></button>
                                </template>
                                <button
                                    class="px-2 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 col-span-2 font-semibold"
                                    @click="setPaidAmount(final_total)">Uang Pas</button>
                                <button
                                    class="px-2 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 col-span-2 font-semibold"
                                    @click="resetAmount()">Reset</button>
                            </div>

                            <!-- Pesan Hutang Otomatis -->
                            <template x-if="paid_amount < final_total && customer">
                                <div
                                    class="text-orange-700 bg-orange-50 border-l-4 border-orange-500 p-2 text-xs mt-3 rounded">
                                    Kekurangan <strong x-text="formatCurrency(final_total - paid_amount)"></strong>
                                    akan otomatis ditambahkan sebagai hutang pelanggan.
                                </div>
                            </template>

                            <!-- Pesan Error -->
                            <template x-if="underpaymentError">
                                <div class="text-red-500 text-sm mt-2">
                                    Uang kurang & tidak ada pelanggan terpilih untuk mencatat hutang!
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Kembalian -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Kembalian</label>
                        <div class="text-xl md:text-2xl font-bold" 
                             :class="{ 'text-red-600': change < 0, 'text-green-600': change >= 0 }"
                             x-text="formatCurrency(change)">
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Catatan</label>
                        <textarea x-model="notes" rows="2"
                            class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"
                            placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="p-4 md:p-5 border-t bg-gray-50 flex flex-col md:flex-row gap-2 md:justify-end">
                    <button @click="showPaymentModal=false"
                        class="px-4 py-2 rounded-lg text-gray-700 bg-gray-200 hover:bg-gray-300 w-full md:w-auto">Batal</button>
                    <button @click="holdSale()"
                        class="px-4 py-2 rounded-lg text-white bg-yellow-500 hover:bg-yellow-600 w-full md:w-auto">Simpan
                        & Tunda</button>
                    <button @click="completePayment()" :disabled="(payment_method === 'debt' && !customer)"
                        class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 w-full md:w-auto">Selesaikan
                        Pembayaran</button>
                </div>
            </div>
        </div>

    </div>


    {{-- Modal Buat Pelanggan (dikontrol oleh Livewire) --}}
    @if ($showCustomerCreateModal)
        <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md"
                @click.away="$wire.set('showCustomerCreateModal', false)">
                <h3 class="text-lg font-bold mb-4">Buat Pelanggan Baru</h3>
                <div class="space-y-4">
                    <div><label for="new_name">Nama Pelanggan</label><input id="new_name" type="text"
                            wire:model="new_customer_name" class="w-full border rounded-lg p-2 mt-1">
                        @error('new_customer_name')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div><label for="new_phone">No. Telepon (Opsional)</label><input id="new_phone" type="text"
                            wire:model="new_customer_phone" class="w-full border rounded-lg p-2 mt-1">
                        @error('new_customer_phone')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-4">
                    <button @click="$wire.set('showCustomerCreateModal', false)"
                        class="px-4 py-2 rounded-lg text-gray-600 bg-gray-100 hover:bg-gray-200">Batal</button>
                    <button wire:click="createNewCustomer"
                        class="px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600">Simpan</button>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    function cartManager() {
        return {
            items: [],
            subtotal: 0,
            final_total: 0,
            transaction_type: 'retail',
            customer: null,
            include_old_debt: false,
            showPaymentModal: false,
            payment_method: 'cash',
            paid_amount: 0,
            paid_amount_display: '0',
            notes: '',
            change: 0,
            underpaymentError: false,
            pending_transaction_id: null, // Lacak ID transaksi pending

            init() {
                this.$watch('paid_amount', () => this.calculateChange());
                this.$watch('final_total', () => this.calculateChange());
                this.$watch('include_old_debt', () => this.calculateFinalTotal());
            },

            addToCart(product) {
                let existingItem = this.items.find(i => i.id === product.id);
                if (existingItem) {
                    if (existingItem.quantity < product.stock) existingItem.quantity++;
                    else window.Livewire.dispatch('show-alert', {
                        type: 'error',
                        message: 'Stok tidak cukup'
                    });
                } else {
                    if (product.stock > 0) {
                        this.items.push({
                            id: product.id,
                            name: product.name,
                            code: product.code,
                            stock: product.stock,
                            retail_price: product.retail_price,
                            wholesale_price: product.wholesale_price,
                            wholesale_min_qty: product.wholesale_min_qty,
                            quantity: 1,
                            price: product.retail_price,
                            subtotal: product.retail_price
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
                    total += this.customer.debt;
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
                // Hanya error jika uang kurang DAN tidak ada pelanggan valid yang dipilih
                if (this.payment_method !== 'debt' && this.paid_amount < this.final_total && (!this.customer || !this
                        .customer.id)) {
                    this.underpaymentError = true;
                    setTimeout(() => this.underpaymentError = false, 3000);
                    return;
                }
                this.underpaymentError = false;

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
                this.items = items;
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
