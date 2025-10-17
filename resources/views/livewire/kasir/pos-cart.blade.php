<div x-data="cartManager()"
    x-init="
        loadCart(@js($initialItems ?? []), @js($initialCustomer ?? null), @js($initialType ?? 'retail'), @js($initialPendingId ?? null));
        $watch('items', () => recalculate());
        Livewire.on('cart:reset', () => resetCart());
    "
    x-on:add-to-cart.window="addToCart($event.detail.product)"
    x-on:customer:selected.window="setCustomer($event.detail.customer)"
    x-on:customer:cleared.window="clearCustomer()"
    x-on:transaction-saved.window="window.open('/kasir-bersama/invoice/' + $event.detail.id, '_blank'); resetCart();"
    class="bg-white rounded-lg shadow p-4 space-y-4">

    <!-- Customer Section -->
    <div class="border-b pb-4">
        <h3 class="text-md font-semibold mb-2">Pelanggan</h3>
        @if (isset($selected_customer_name) && $selected_customer_name)
            <div class="flex items-center justify-between bg-blue-100 border border-blue-200 rounded-lg p-2">
                <p class="font-semibold text-blue-700">{{ $selected_customer_name }}</p>
                <button wire:click="clearCustomer" class="p-1 text-red-500 hover:text-red-700 rounded-full hover:bg-red-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        @else
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="customer_search" placeholder="Cari atau tambah pelanggan..." class="w-full pl-4 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if (isset($customer_search) && strlen($customer_search) >= 2)
                    <div class="absolute z-20 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        @forelse($customers as $customer)
                            <div wire:click="selectCustomer({{ $customer->id }}, '{{ addslashes($customer->name) }}')" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                                <p class="font-semibold">{{ $customer->name }}</p>
                                <p class="text-sm text-gray-600">{{ $customer->phone }}</p>
                            </div>
                        @empty
                            <div class="px-4 py-2 text-gray-500">Pelanggan tidak ditemukan.</div>
                        @endforelse
                        <div wire:click="$set('showCustomerCreateModal', true)" class="px-4 py-3 text-center text-blue-600 font-semibold cursor-pointer border-t hover:bg-gray-50 rounded-b-lg">
                            + Buat Pelanggan Baru
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Cart Items Section -->
    <div>
        <h2 class="text-lg font-bold mb-2">🛒 Keranjang</h2>
        <div class="max-h-60 overflow-y-auto space-y-2 pr-2">
            <template x-if="items.length === 0">
                <p class="text-gray-500 text-center py-4">Keranjang kosong</p>
            </template>
            <template x-for="item in items" :key="item.id">
                <div class="flex justify-between items-center border-b py-3">
                    <div class="flex flex-col">
                        <p x-text="item.name" class="font-semibold text-gray-800"></p>
                        <p class="text-xs text-gray-500 mt-1" x-text="formatCurrency(item.price)"></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="decrement(item.id)" class="w-7 h-7 flex items-center justify-center bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 active:scale-95 transition">−</button>
                        <input type="text" x-model.number="item.quantity" @change="validateAndRecalculate(item.id)" class="w-12 text-center border-gray-300 rounded-lg py-1 text-sm font-medium focus:ring-2 focus:ring-blue-400">
                        <button @click="increment(item.id)" class="w-7 h-7 flex items-center justify-center bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 active:scale-95 transition">+</button>
                        <button @click="remove(item.id)" class="w-7 h-7 flex items-center justify-center bg-red-100 text-red-600 rounded-full hover:bg-red-200 active:scale-95 transition">🗑️</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Total & Actions -->
    <div class="border-t pt-4 space-y-3">
        <div class="flex justify-between items-center">
            <span class="font-medium">Tipe Transaksi:</span>
            <div class="flex items-center gap-2">
                <span :class="transaction_type === 'retail' ? 'text-blue-600 font-bold' : 'text-gray-500'">Eceran</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" x-model="transaction_type" true-value="wholesale" false-value="retail" @change="recalculate()" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                </label>
                <span :class="transaction_type === 'wholesale' ? 'text-blue-600 font-bold' : 'text-gray-500'">Grosir</span>
            </div>
        </div>

        <div class="flex justify-between items-center font-semibold">
            <span class="text-gray-600">Subtotal</span>
            <span class="text-lg" x-text="formatCurrency(subtotal)"></span>
        </div>

        <template x-if="customer && customer.debt > 0">
            <div class="flex justify-between items-center font-semibold border-t pt-2">
                <div class="text-gray-600">
                    <p>Hutang Lama</p>
                    <button @click="include_old_debt = !include_old_debt" class="text-xs font-normal" :class="include_old_debt ? 'text-red-500' : 'text-blue-500'" x-text="include_old_debt ? '[ Jangan Sertakan ]' : '[ Sertakan ke Tagihan ]'"></button>
                </div>
                <span class="text-lg" x-text="formatCurrency(customer.debt)"></span>
            </div>
        </template>

        <div class="flex justify-between items-center font-bold text-2xl text-blue-600 border-t pt-2">
            <span>Total</span>
            <span x-text="formatCurrency(final_total)"></span>
        </div>

        <div class="flex justify-between gap-2">
            <button @click="resetCart()" class="w-1/3 bg-red-100 text-red-600 font-semibold py-3 rounded-lg hover:bg-red-200 disabled:opacity-50" :disabled="items.length === 0">Batal</button>
            <button @click="showPaymentModal = true" :disabled="items.length === 0" class="w-2/3 bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 disabled:bg-gray-300">Bayar</button>
        </div>
    </div>

    <!-- Payment Modal -->
    <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-75 p-4">
        <!-- ... (Konten modal sama seperti di pos/cart.blade.php) ... -->
    </div>

    <!-- Customer Create Modal -->
    @if (isset($showCustomerCreateModal) && $showCustomerCreateModal)
        <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-[70] p-4">
            <!-- ... (Konten modal sama seperti di pos/cart.blade.php) ... -->
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Skrip cartManager() yang lengkap dari pos/cart.blade.php akan disisipkan di sini
    // untuk memastikan semua fungsionalitas berjalan.
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
            pending_transaction_id: null,

            init() {
                this.$watch('paid_amount', () => this.calculateChange());
                this.$watch('final_total', () => this.calculateChange());
                this.$watch('include_old_debt', () => this.calculateFinalTotal());
            },

            addToCart(product) {
                let existingItem = this.items.find(i => i.id === product.id);
                if (existingItem) {
                    if (existingItem.quantity < product.stock) existingItem.quantity++;
                    else this.showAlert('error', 'Stok tidak cukup');
                } else {
                    if (product.stock > 0) {
                        this.items.push({
                            id: product.id, name: product.name, code: product.code, stock: product.stock,
                            retail_price: product.retail_price, wholesale_price: product.wholesale_price,
                            wholesale_min_qty: product.wholesale_min_qty, quantity: 1, price: product.retail_price,
                            subtotal: product.retail_price
                        });
                    }
                }
            },

            increment(id) {
                let item = this.items.find(i => i.id === id);
                if (item && item.quantity < item.stock) item.quantity++;
            },
            decrement(id) {
                let item = this.items.find(i => i.id === id);
                if (item && item.quantity > 1) item.quantity--;
                else if (item) this.remove(id);
            },
            remove(id) {
                this.items = this.items.filter(i => i.id !== id);
            },

            validateAndRecalculate(id) {
                let item = this.items.find(i => i.id === id);
                if (!item) return;
                let qty = parseFloat(String(item.quantity).replace(',', '.'));
                if (isNaN(qty) || qty <= 0) { this.remove(id); return; }
                if (qty > item.stock) {
                    item.quantity = item.stock;
                    this.showAlert('error', 'Stok tidak cukup');
                }
            },

            recalculate() {
                let currentSubtotal = 0;
                this.items.forEach(item => {
                    let useWholesale = this.transaction_type === 'wholesale' && item.quantity >= item.wholesale_min_qty;
                    item.price = useWholesale ? item.wholesale_price : item.retail_price;
                    item.subtotal = item.price * item.quantity;
                    currentSubtotal += item.subtotal;
                });
                this.subtotal = currentSubtotal;
                this.calculateFinalTotal();
            },

            setCustomer(customer) { this.customer = customer; },
            clearCustomer() { this.customer = null; this.include_old_debt = false; },

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
            setPaidAmount(amount) {
                this.paid_amount = amount;
                this.paid_amount_display = amount.toLocaleString('id-ID');
            },
            resetAmount() { this.paid_amount = 0; this.paid_amount_display = '0'; },

            calculateChange() {
                if (this.payment_method === 'debt') { this.change = 0; return; }
                this.change = this.paid_amount - this.final_total;
            },

            completePayment() {
                if (this.payment_method !== 'debt' && this.paid_amount < this.final_total) {
                    this.underpaymentError = true;
                    setTimeout(() => this.underpaymentError = false, 3000);
                    return;
                }
                this.underpaymentError = false;
                this.processPayment('complete');
            },

            holdSale() { this.processPayment('pending'); },

            processPayment(status) {
                let paymentDetails = {
                    customer: this.customer,
                    subtotal: this.subtotal, final_total: this.final_total, status: status,
                    include_old_debt: this.include_old_debt, payment_method: this.payment_method,
                    paid_amount: this.payment_method === 'debt' ? 0 : this.paid_amount,
                    change: this.change > 0 ? this.change : 0, notes: this.notes,
                    transaction_type: this.transaction_type, pending_id: this.pending_transaction_id
                };
                if (status === 'complete') {
                    this.$wire.processPaymentFinal(this.items.map(i => ({id: i.id, quantity: i.quantity, price: i.price, subtotal: i.subtotal})), paymentDetails);
                } else {
                    this.$wire.holdTransaction(this.items.map(i => ({id: i.id, quantity: i.quantity, price: i.price, subtotal: i.subtotal})), paymentDetails);
                }
            },

            resetCart() {
                this.items = []; this.notes = ''; this.showPaymentModal = false;
                this.pending_transaction_id = null; this.clearCustomer();
            },

            loadCart(items, customer, type, pending_id = null) {
                if (items && items.length > 0) {
                    this.items = items.map(i => ({...i, subtotal: i.price * i.quantity}));
                }
                this.transaction_type = type || 'retail';
                if (customer) { this.setCustomer(customer); }
                this.pending_transaction_id = pending_id;
                this.recalculate();
            },

            formatCurrency(amount) {
                if (typeof amount !== 'number') { amount = 0; }
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
            },

            showAlert(type, message) {
                window.Livewire.dispatch('showAlert', { type: type, message: message });
            }
        }
    }
</script>
@endpush