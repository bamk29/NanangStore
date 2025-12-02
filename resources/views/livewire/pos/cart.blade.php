<div x-data="cartManager()" x-init="loadCart(@js($initialItems), @js($initialCustomer), @js($initialType), @js($initialPendingId))"
    x-on:add-to-cart.window="addToCart($event.detail.product, $event.detail.quantity)"
    x-on:customer:selected.window="setCustomer($event.detail.customer)" x-on:customer:cleared.window="customer = null; include_old_debt = false; calculateFinalTotal();"
    x-on:transaction-saved.window="window.location.href = '/pos/invoice/' + $event.detail.id"
    x-on:cart:reset.window="resetCart()" x-on:customer-selected-in-modal.window="showCustomerWarningModal = false"
    @shortcut:pay.window="initiatePayment()" @shortcut:hold.window="initiateHold()"
    @shortcut:reduction.window="toggleManualReduction()" @shortcut:quantity.window="focusLastItemQuantity()"
    @shortcut:customer.window="focusCustomerSearch()"
    @keydown.escape.window="handleEscape()" class="flex flex-col h-full bg-white">
    <!-- Tombol Eceran/Grosir & Pelanggan -->
    <div class="p-2 bg-gray-100 flex-shrink-0 border-b">
        <div class="grid grid-cols-2 gap-1 bg-gray-200 p-1 rounded-lg mb-2">
            <button @click="transaction_type = 'retail'; recalculate()"
                :class="{
                    'bg-blue-600 text-white shadow': transaction_type === 'retail',
                    'bg-white text-gray-700': transaction_type !== 'retail'
                }"
                class="px-4 py-2 text-sm font-bold rounded-md focus:outline-none transition-colors duration-200">Eceran</button>
            <button @click="transaction_type = 'wholesale'; recalculate()"
                :class="{
                    'bg-green-600 text-white shadow': transaction_type === 'wholesale',
                    'bg-white text-gray-700': transaction_type !== 'wholesale'
                }"
                class="px-4 py-2 text-sm font-bold rounded-md focus:outline-none transition-colors duration-200">Grosir</button>
        </div>
        <!-- Customer Display -->
        <div class="bg-white rounded-lg">
            <template x-if="customer">
                <div class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-md px-2 py-1">
                    <div class="flex items-center min-w-0">
                        <span class="text-xs text-blue-600 mr-2 flex-shrink-0">Pelanggan:</span>
                        <p class="font-semibold text-sm text-blue-800 truncate" x-text="customer.name"></p>
                    </div>
                    <button @click="clearCustomer()" class="p-1 text-red-500 hover:text-red-700 rounded-full hover:bg-red-100 ml-2 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </template>
            <template x-if="!customer">
                <button @click="showCustomerWarningModal = true"
                    class="w-full text-left px-2 py-1.5 border border-dashed rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="flex items-center text-gray-500">
                        <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        <span class="font-medium text-sm">Pilih Pelanggan</span>
                    </div>
                </button>
            </template>
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
                <div class="p-1 flex items-center space-x-1 my-1">
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-xs text-gray-800 truncate" x-text="item.name"></p>
                        <p class="text-xs text-gray-500" x-text="formatCurrency(item.price)"></p>
                    </div>
                    <div class="flex items-center">
                        <button @click="decrement(item.id)"
                            class="w-9 h-9 flex items-center justify-center mx-1 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 active:scale-95 transition-all duration-150 focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4">
                                </path>
                            </svg>
                        </button>
                        <div class="relative w-auto mx-1">

                            <input type="text" inputmode="decimal" x-model="item.quantity"
                                @input="validateAndRecalculate(item.id)"
                                class="w-16 h-9 text-center bg-transparent text-base font-semibold text-gray-800 focus:outline-none focus:ring-0">
                        </div>
                        <button @click="increment(item.id)"
                            class="w-9 h-9 flex items-center justify-center mx-1 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 active:scale-95 transition-all duration-150 focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="w-18 text-right font-semibold text-sm" x-text="formatCurrency(item.subtotal)"></div>
                    <button @click="remove(item.id)" class="flex-shrink-0 text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50"><svg
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
        <div class="p-2 bg-gray-50 space-y-2">
            <div class="flex justify-between items-baseline font-bold text-blue-600">
                <span class="text-sm text-gray-600" x-text="items.length + ' item'"></span>
                <span class="text-2xl" x-text="formatCurrency(final_total)"></span>
            </div>

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
        </div>

        <!-- Bayar Button -->
        <div class="p-2 bg-gray-50 border-t grid grid-cols-2 gap-2">
            <button @click="initiateHold()" :disabled="items.length === 0"
                class="w-full bg-yellow-500 text-white font-bold py-3 rounded-lg hover:bg-yellow-600 disabled:bg-gray-300 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Tunda</span>
                <kbd
                    class="ml-2 px-2 py-0.5 text-xs font-semibold text-yellow-800 bg-yellow-200 border border-yellow-300 rounded">F4</kbd>
            </button>
            <button @click="initiatePayment()" :disabled="items.length === 0"
                class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 disabled:bg-gray-300 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H7a3 3 0 00-3 3v8a3 3 0 003 3z">
                    </path>
                </svg>
                <span>Bayar</span>
                <kbd
                    class="ml-2 px-2 py-0.5 text-xs font-semibold text-blue-800 bg-blue-200 border border-blue-300 rounded">F2</kbd>
            </button>
        </div>
    </div>

    <!-- Modals -->
    <!-- Modal Pilih Pelanggan -->
    <div x-show="showCustomerWarningModal" x-cloak
        class="fixed inset-0 z-50 flex items-start sm:items-center justify-center bg-black/60 backdrop-blur-sm p-4 pt-20">
        <div @click.away="showCustomerWarningModal = false"
            @keydown.F2.prevent="skipToPay()"
            @keydown.F4.prevent="skipToHold()"
            class="bg-white rounded-2xl shadow-xl w-full max-w-md transform transition-all">

            <div class="p-6 relative">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Pilih atau Buat Pelanggan</h3>
                <button @click="showCustomerWarningModal = false" class="absolute top-3 right-3 p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded-r-lg mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.21 3.001-1.742 3.001H4.42c-1.532 0-2.492-1.667-1.742-3.001l5.58-9.92zM10 13a1 1 0 110-2 1 1 0 010 2zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">Transaksi tanpa pelanggan tidak akan tercatat dalam
                                riwayat hutang atau poin loyalitas.</p>
                        </div>
                    </div>
                </div>
                <div x-data="customerSearch()" class="relative" :class="{'mb-52': isOpen}">
                    <input type="text" x-ref="customerSearchInput" x-model.debounce.300ms="searchQuery"
                        @focus="handleFocus()" @keydown="handleKeydown($event)" @keydown.escape.stop="isOpen = false"
                        placeholder="Cari pelanggan (nama/telp)..."
                        class="w-full pl-4 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div x-show="isOpen" x-transition
                        class="absolute z-10 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <template x-if="isLoading">
                            <div class="px-4 py-2 text-gray-500">Mencari...</div>
                        </template>
                        <template x-for="(customer, index) in results" :key="customer.id">
                            <div @click="selectCustomer(customer)" @mouseenter="selectedIndex = index"
                                :class="{ 'bg-blue-100': index === selectedIndex }"
                                class="px-4 py-2 cursor-pointer hover:bg-gray-100">
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
            <div class="px-6 pb-4 space-y-3 border-t bg-gray-50 rounded-b-2xl pt-4">
                <!-- New Buttons -->
                <div class="grid grid-cols-2 gap-2">
                    <button @click="skipToHold()"
                    class="w-full px-4 py-2.5 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 focus:outline-none flex items-center justify-center gap-2">
                    <span>Tunda</span>
                    <kbd class="px-1.5 py-0.5 text-xs font-sans font-semibold text-yellow-800 bg-yellow-200 border border-yellow-300 rounded-md">F4</kbd>
                </button>
                <button @click="skipToPay()"
                    class="w-full px-4 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none flex items-center justify-center gap-2">
                    <span>Bayar</span>
                    <kbd class="px-1.5 py-0.5 text-xs font-sans font-semibold text-blue-800 bg-blue-200 border border-blue-300 rounded-md">F2</kbd>
                </button>
                </div>
                <!-- Existing Close Button -->
                <button @click="showCustomerWarningModal = false"
                    class="w-full px-4 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 focus:outline-none">
                    Tutup
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
                    @keydown.enter.prevent="completePayment()"
                    class="relative bg-white w-full max-w-2xl flex flex-col max-h-[90vh] transform transition-all duration-300 ease-out rounded-t-2xl sm:rounded-2xl sm:mb-0 mb-24">
            <div class="p-4 md:p-5 border-b bg-gray-50 flex justify-between items-center">
                <h3 class="text-lg md:text-xl font-bold text-gray-800">💰 Detail Pembayaran</h3>
                <button @click="showPaymentModal = false"
                    class="-mr-2 p-2 text-gray-500 hover:text-gray-800 rounded-full hover:bg-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-4 md:p-6 space-y-4 overflow-y-auto flex-1">
                <!-- Total Section -->
                <div class="text-center">
                    <p class="text-gray-500 text-sm md:text-base">Total Awal</p>
                    <p class="text-2xl md:text-3xl font-bold text-gray-500 transition-all"
                       :class="{'line-through': manualReductionAmount > 0}"
                       x-text="formatCurrency(finalTotalBeforeReduction)"></p>
                </div>

                <!-- Manual Reduction Section -->
                <div>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" x-model="showManualReductionFields" class="form-checkbox h-5 w-5 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Tambah Potongan Manual</span>
                    </label>
                </div>

                <template x-if="showManualReductionFields">
                    <div x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         class="p-4 bg-gray-50 rounded-lg space-y-4 border">
                        <div>
                            <label class="block text-sm font-semibold mb-1">Jumlah Potongan Manual</label>
                            <input type="text" x-model.number="manualReductionAmount"
                                class="w-full border rounded-lg p-2 font-bold text-lg text-right focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                placeholder="0">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Keterangan Potongan Manual</label>
                            <textarea x-model="manualReductionNotes" rows="2"
                                class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"
                                placeholder="Contoh: Diskon spesial, Tukar tambah..."></textarea>
                        </div>
                        
                        <!-- Discount Allocation Checkboxes -->
                        <template x-if="manualReductionAmount > 0">
                            <div class="border-t pt-3 space-y-2">
                                <p class="text-sm font-semibold text-gray-700">Potong dari:</p>
                                <div class="space-y-2">
                                    <label class="flex items-center cursor-pointer hover:bg-gray-100 p-2 rounded transition">
                                        <input type="checkbox" x-model="discountForBakso" 
                                               class="form-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 rounded">
                                        <span class="ml-2 text-sm text-gray-700">🥩 Giling Bakso</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer hover:bg-gray-100 p-2 rounded transition">
                                        <input type="checkbox" x-model="discountForNanang" 
                                               class="form-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 rounded">
                                        <span class="ml-2 text-sm text-gray-700">🏪 Toko Nanang</span>
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 italic">
                                    💡 Centang bisnis unit yang akan menerima potongan. Jika tidak ada yang dicentang, potongan akan dibagi proporsional.
                                </p>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- New Final Total Section -->
                <div class="text-center p-3 bg-blue-50 rounded-lg border-t-4 border-b-4 border-blue-200">
                    <p class="text-blue-600 text-sm md:text-base font-semibold">TOTAL TAGIHAN AKHIR</p>
                    <p class="text-3xl md:text-4xl font-bold text-blue-700" x-text="formatCurrency(final_total)"></p>
                </div>
                
                <!-- Payment Method Section -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="method in ['cash', 'transfer', 'debt']" :key="method">
                            <label class="p-3 border rounded-lg text-center cursor-pointer select-none transition"
                                :class="{ 
                                    'bg-blue-600 text-white border-blue-600': payment_method === method, 
                                    'bg-gray-100 hover:bg-gray-200 text-gray-700': payment_method !== method && (method !== 'debt' || customer),
                                    'opacity-50 cursor-not-allowed bg-gray-100 text-gray-400': method === 'debt' && !customer
                                }">
                                <input type="radio" class="hidden" x-model="payment_method" :value="method" :disabled="method === 'debt' && !customer">
                                <span x-text="method === 'cash' ? 'Tunai' : method === 'transfer' ? 'Transfer' : 'Hutang'"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Paid Amount Section -->
                <template x-if="payment_method !== 'debt'">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Uang Dibayarkan</label>
                        <input type="text" x-ref="paidAmountInput" x-model="paid_amount_display"
                            @input="formatPaidAmount($event)" placeholder="0"
                            class="w-full border rounded-lg p-2 font-bold text-lg text-right focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                        <div class="mt-3 grid grid-cols-4 gap-2 text-sm">
                            <template x-for="val in [500, 1000, 2000, 5000, 10000, 20000, 50000, 100000]"
                                :key="val">
                                <button class="px-2 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 font-semibold"
                                    @click="addPaidAmount(val)" x-text="val.toLocaleString('id-ID')"></button>
                            </template>
                            <button class="px-2 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 col-span-2 font-semibold" @click="setPaidAmount(final_total)">Uang Pas</button>
                            <button class="px-2 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 col-span-2 font-semibold" @click="resetAmount()">Reset</button>
                        </div>
                        <template x-if="paid_amount < final_total && customer">
                            <div class="text-orange-700 bg-orange-50 border-l-4 border-orange-500 p-2 text-xs mt-3 rounded">
                                Kekurangan <strong x-text="formatCurrency(final_total - paid_amount)"></strong> akan
                                otomatis ditambahkan sebagai hutang pelanggan.
                            </div>
                        </template>
                        <template x-if="underpaymentError">
                            <div class="text-red-500 text-sm mt-2">Uang kurang & tidak ada pelanggan terpilih untuk
                                mencatat hutang!</div>
                        </template>
                    </div>
                </template>

                <!-- Change Section -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Kembalian</label>
                    <div class="text-4xl md:text-5xl font-bold"
                        :class="{ 'text-red-600': change < 0, 'text-green-600': change >= 0 }"
                        x-text="formatCurrency(change)"></div>
                </div>

                <!-- Notes Section -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Catatan Transaksi Umum</label>
                    <textarea x-model="notes" rows="2"
                        class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"
                        placeholder="Catatan untuk transaksi ini..."></textarea>
                </div>
            </div>
            <div class="p-4 md:p-5 border-t bg-gray-50 flex flex-col md:flex-row gap-2 md:justify-end">
                <button @click="showPaymentModal=false"
                    class="px-4 py-2 rounded-lg text-gray-700 bg-gray-200 hover:bg-gray-300 w-full md:w-auto">Batal</button>
                <button @click="holdSale()"
                    class="px-4 py-2 rounded-lg text-white bg-yellow-500 hover:bg-yellow-600 w-full md:w-auto">Simpan &
                    Tunda</button>
                <button @click="completePayment()"
                    :disabled="(payment_method === 'debt' && !customer) || isProcessingPayment"
                    class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-wait w-full md:w-auto flex items-center justify-center">
                    <template x-if="isProcessingPayment">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span>Memproses...</span>
                    </template>
                    <template x-if="!isProcessingPayment">
                        <span class="flex items-center justify-center gap-2">
                            <span>Selesaikan Pembayaran</span>
                            <kbd
                                class="px-2 py-0.5 text-xs font-sans font-semibold text-blue-800 bg-blue-200 border border-blue-300 rounded">Enter</kbd>
                        </span>
                    </template>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Kekurangan Bayar -->
    <div x-show="showUnderpaymentConfirmation" x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/30 p-4"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div @click.away="showUnderpaymentConfirmation = false" @keydown.enter.window.prevent="if(showUnderpaymentConfirmation) proceedWithUnderpayment()"
            class="bg-white rounded-2xl shadow-xl w-full max-w-sm transform transition-all"
            x-show="showUnderpaymentConfirmation" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">Pembayaran Kurang!</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Uang yang dibayarkan lebih kecil dari total tagihan. Kekurangan sebesar <strong
                        x-text="formatCurrency(final_total - paid_amount)"></strong> akan ditambahkan ke hutang
                    pelanggan.
                </p>
                <p class="mt-1 text-sm text-gray-600">
                    Lanjutkan transaksi?
                </p>
            </div>
            <div
                class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 rounded-b-2xl">
                <button @click="showUnderpaymentConfirmation = false" type="button"
                    class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                    Batal
                </button>
                <button x-ref="confirmDebtButton" @click="proceedWithUnderpayment()" type="button"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2.5 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Tunda Transaksi -->
    <div x-show="showHoldConfirmation" x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/30 p-4"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div @click.away="showHoldConfirmation = false"
             @keydown.enter.window.prevent="if(showHoldConfirmation) { holdSale(); showHoldConfirmation = false; }"
             class="bg-white rounded-2xl shadow-xl w-full max-w-sm transform transition-all">

            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">Tunda Transaksi Ini?</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Keranjang saat ini akan disimpan di daftar "Transaksi Tertunda" dan keranjang akan dikosongkan.
                </p>
                <p class="text-xs text-gray-500 mt-4">Tekan <kbd
                        class="px-1.5 py-0.5 text-xs font-sans font-semibold text-gray-800 bg-gray-100 border border-gray-300 rounded-md">Enter</kbd>
                    untuk konfirmasi atau <kbd
                        class="px-1.5 py-0.5 text-xs font-sans font-semibold text-gray-800 bg-gray-100 border border-gray-300 rounded-md">Esc</kbd>
                    untuk batal.</p>
            </div>
            <div
                class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 rounded-b-2xl">
                <button @click="showHoldConfirmation = false" type="button"
                    class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                    Batal
                </button>
                <button x-ref="confirmHoldButton" @click="holdSale(); showHoldConfirmation = false;" type="button"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2.5 bg-yellow-500 text-base font-medium text-white hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:w-auto sm:text-sm">
                    Ya, Tunda Transaksi
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Buat Pelanggan (dikontrol oleh Livewire) --}}
    @if ($showCustomerCreateModal)
        <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4"
            @keydown.escape.window="$wire.set('showCustomerCreateModal', false)">
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
                        class="px-4 py-2.5 rounded-lg text-gray-600 bg-gray-100 hover:bg-gray-200">Batal</button>
                    <button wire:click="createNewCustomer"
                        class="px-4 py-2.5 rounded-lg bg-blue-500 text-white hover:bg-blue-600">Simpan</button>
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
            selectedIndex: -1,
            init() {
                this.$watch('searchQuery', (value) => {
                    if (value.length >= 0) {
                        this.selectedIndex = -1;
                        this.fetchCustomers();
                    } else {
                        this.results = [];
                        this.isOpen = false;
                    }
                });
                // Listen for the global focus event
                window.addEventListener('focus-customer-search', () => {
                    this.$nextTick(() => {
                        if (this.$refs.customerSearchInput) {
                            this.$refs.customerSearchInput.focus();
                        }
                    });
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
            handleKeydown(e) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.selectedIndex = (this.selectedIndex + 1) % this.results.length;
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    this.selectedIndex = (this.selectedIndex - 1 + this.results.length) % this.results.length;
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (this.selectedIndex !== -1) {
                        this.selectCustomer(this.results[this.selectedIndex]);
                    }
                }
            },
            selectCustomer(customer) {
                this.isOpen = false;
                this.$dispatch('customer-selected-in-modal');

                setTimeout(() => {
                    this.$wire.selectCustomerFromSearch(customer);
                    this.searchQuery = '';
                    this.results = [];
                }, 150);
            }
        }
    }

    function cartManager() {
        return {
            items: [],
            subtotal: 0,
            final_total: 0,
            finalTotalBeforeReduction: 0, // New property
            transaction_type: 'wholesale',
            customer: null,
            include_old_debt: false,
            showPaymentModal: false,
            showCustomerWarningModal: false,
            showUnderpaymentConfirmation: false,
            showHoldConfirmation: false,
            payment_method: 'cash',
            paid_amount: 0,
            paid_amount_display: '0',
            notes: '',
            change: 0,
            underpaymentError: false,
            pending_transaction_id: null, // Lacak ID transaksi pending
            isProcessingPayment: false,
            finalTotalBeforeReduction: 0,
            manualReductionAmount: 0,
            manualReductionNotes: '',
            showManualReductionFields: false,
            discountForBakso: false, // Checkbox for Giling Bakso discount
            discountForNanang: false, // Checkbox for Nanang Store discount

            init() {
                this.$watch('paid_amount', () => this.calculateChange());
                this.$watch('final_total', () => this.calculateChange());
                this.$watch('include_old_debt', () => this.calculateFinalTotal());
                this.$watch('manualReductionAmount', () => this.calculateFinalTotal());
                this.$watch('showManualReductionFields', (value) => {
                    if (!value) {
                        this.manualReductionAmount = 0;
                        this.manualReductionNotes = '';
                    }
                    this.calculateFinalTotal();
                });

                this.$watch('showPaymentModal', (value) => {
                    if (value) {
                        this.showCustomerWarningModal = false; // Force close
                        setTimeout(() => {
                            this.$nextTick(() => {
                                if (this.$refs.paidAmountInput) {
                                    this.$refs.paidAmountInput.focus();
                                    this.$refs.paidAmountInput.select();
                                }
                            });
                        }, 100);
                    } else {
                        this.isProcessingPayment = false;
                    }
                });

                this.$watch('showHoldConfirmation', (value) => {
                    if (value) {
                        this.showCustomerWarningModal = false; // Force close
                        this.$nextTick(() => {
                            if (this.$refs.confirmHoldButton) {
                                this.$refs.confirmHoldButton.focus();
                            }
                        });
                    }
                });

                this.$watch('showUnderpaymentConfirmation', (value) => {
                    if (value) {
                        this.$nextTick(() => {
                            if (this.$refs.confirmDebtButton) {
                                this.$refs.confirmDebtButton.focus();
                            }
                        });
                    }
                });

                this.$watch('showCustomerWarningModal', (value) => {
                    if (value) {
                        window.dispatchEvent(new CustomEvent('focus-customer-search'));
                    }
                });
            },

            handleEscape() {
                if (this.showUnderpaymentConfirmation) {
                    this.showUnderpaymentConfirmation = false;
                } else if (this.showHoldConfirmation) {
                    this.showHoldConfirmation = false;
                } else if (this.showPaymentModal) {
                    this.showPaymentModal = false;
                } else if (this.showCustomerWarningModal) {
                    this.showCustomerWarningModal = false;
                }
            },

            initiatePayment() {
                if (this.items.length === 0) return;
                this.recalculate(); // Recalculate everything first

                if (!this.customer) {
                    this.showCustomerWarningModal = true;
                } else {
                    this.showPaymentModal = true;
                }
            },

            initiateHold() {
                if (this.items.length === 0) return;
                if (!this.customer) {
                    this.showCustomerWarningModal = true;
                } else {
                    this.showHoldConfirmation = true;
                }
            },

            skipToPay() {
                this.showCustomerWarningModal = false;
                this.$nextTick(() => {
                    this.showPaymentModal = true;
                });
            },

            skipToHold() {
                this.showCustomerWarningModal = false;
                this.$nextTick(() => {
                    this.showHoldConfirmation = true;
                });
            },

            addToCart(product, quantity = 1) {
                let existingItem = this.items.find(i => i.id === product.id);
                const newQuantity = parseFloat(quantity) || 1;

                if (existingItem) {
                    const totalQuantity = parseFloat(String(existingItem.quantity).replace(',', '.')) + newQuantity;
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
                    const useWholesale = this.transaction_type === 'wholesale' && item.quantity >= item.wholesale_min_qty;
                    item.price = useWholesale ? item.wholesale_price : item.retail_price;
                    item.subtotal = item.price * item.quantity;
                    currentSubtotal += item.subtotal;
                });

                this.subtotal = currentSubtotal;

                this.calculateFinalTotal();
                this.$dispatch('cart-updated', {
                    items: this.items,
                    total: this.final_total
                });
            },

            setCustomer(customer) {
                this.customer = customer;
                if (this.customer && this.customer.debt > 0) {
                    this.include_old_debt = true;
                } else {
                    this.include_old_debt = false;
                }
                this.calculateFinalTotal();
            },

            clearCustomer() {
                this.$wire.clearCustomer();
            },



            calculateFinalTotal() {
                // The subtotal already has wholesale discounts applied.
                // We start with that, then add debt, then subtract any *manual* reduction.
                let total = this.subtotal; 
                
                if (this.include_old_debt && this.customer && this.customer.debt > 0) {
                    total += parseFloat(this.customer.debt);
                }

                // This is the total before any *manual* reduction is applied.
                this.finalTotalBeforeReduction = total;
                
                // Apply manual reduction if enabled and valid
                if (this.showManualReductionFields && this.manualReductionAmount > 0) {
                    total -= parseFloat(this.manualReductionAmount);
                }

                // Ensure final_total doesn't go below 0 and set it
                this.final_total = Math.max(0, total);
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
                if (this.paid_amount < this.final_total && (!this.customer || !this.customer.id)) {
                    this.underpaymentError = true;
                    setTimeout(() => this.underpaymentError = false, 3000);
                    return;
                }
                if (this.paid_amount < this.final_total && this.customer && this.customer.id) {
                    this.showUnderpaymentConfirmation = true;
                    return;
                }
                this.executePayment();
            },

            proceedWithUnderpayment() {
                this.showUnderpaymentConfirmation = false;
                this.executePayment();
            },

            executePayment() {
                this.underpaymentError = false;
                this.isProcessingPayment = true;
                let paymentDetails = {
                    customer: this.customer,
                    subtotal: this.subtotal,
                    final_total: this.final_total,
                    include_old_debt: this.include_old_debt,
                    payment_method: this.payment_method,
                    paid_amount: this.paid_amount,
                    change: this.change > 0 ? this.change : 0,
                    notes: this.notes,
                    transaction_type: this.transaction_type,
                    pending_id: this.pending_transaction_id,
                    total_reduction_amount: this.manualReductionAmount || 0,
                    reduction_notes: this.manualReductionNotes,
                    discount_for_bakso: this.discountForBakso,
                    discount_for_nanang: this.discountForNanang,
                };
                this.$wire.processPaymentFinal(this.items, paymentDetails);
            },

            holdSale() {
                let paymentDetails = {
                    customer: this.customer,
                    subtotal: this.subtotal,
                    transaction_type: this.transaction_type,
                    notes: this.notes,
                    total_reduction_amount: this.manualReductionAmount || 0,
                    reduction_notes: this.manualReductionNotes,
                };
                this.$wire.holdTransaction(this.items, paymentDetails);
            },

            resetCart() {
                this.items = [];
                this.recalculate();
                this.clearCustomer();
                this.notes = '';
                this.showPaymentModal = false;
                this.showHoldConfirmation = false;
                this.pending_transaction_id = null;
                this.manualReductionAmount = 0;
                this.manualReductionNotes = '';
                this.showManualReductionFields = false;
                this.discountForBakso = false;
                this.discountForNanang = false;
            },

            loadCart(items, customer, type, pending_id = null) {
                this.items = items.map(item => {
                    item.quantity = parseFloat(item.quantity);
                    return item;
                });
                this.transaction_type = type;
                if (customer) {
                    this.setCustomer(customer);
                }
                this.pending_transaction_id = pending_id;
                this.recalculate();
            },

            formatCurrency(amount) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(amount);
            },

            // Shortcuts
            toggleManualReduction() {
                this.showManualReductionFields = !this.showManualReductionFields;
                if (this.showManualReductionFields) {
                    this.$nextTick(() => {
                        if (this.$refs.manualReductionInput) this.$refs.manualReductionInput.focus();
                    });
                }
            },
            focusLastItemQuantity() {
                if (this.items.length > 0) {
                    // Focus the last item's quantity input
                    // We need to find the input element. Since we iterate, we can use ID or index.
                    // The inputs have x-model="item.quantity". We can add x-ref to them or use querySelector.
                    // Let's use a dynamic ref or class.
                    // Actually, let's just use document.querySelectorAll for simplicity in this context
                    const inputs = document.querySelectorAll('input[x-model="item.quantity"]');
                    if (inputs.length > 0) {
                        const lastInput = inputs[inputs.length - 1];
                        lastInput.focus();
                        lastInput.select();
                    }
                }
            },
            focusCustomerSearch() {
                // Dispatch event to focus customer search (handled in customerSearch component)
                window.dispatchEvent(new CustomEvent('focus-customer-search'));
            },

            // WhatsApp Share
            shareBill() {
                if (this.items.length === 0) return;
                
                let text = `*Struk Belanja Sementara*\n`;
                text += `*${new Date().toLocaleString('id-ID')}*\n\n`;
                
                this.items.forEach(item => {
                    text += `${item.name}\n`;
                    text += `${item.quantity} x ${this.formatCurrency(item.price)} = ${this.formatCurrency(item.subtotal)}\n`;
                });
                
                text += `\n--------------------------------\n`;
                text += `*Total: ${this.formatCurrency(this.final_total)}*\n`;
                
                if (this.customer) {
                    text += `Pelanggan: ${this.customer.name}\n`;
                }
                
                this.copyToClipboard(text);
            },

            shareDebt() {
                if (!this.customer || this.customer.debt <= 0) return;
                
                let text = `Halo Kak *${this.customer.name}*,\n\n`;
                text += `Kami dari *NanangStore* ingin menginformasikan rincian tagihan Anda:\n\n`;
                text += `Total Tagihan: *${this.formatCurrency(this.customer.debt)}*\n\n`;
                text += `Mohon kesediaannya untuk melakukan pembayaran. Terima kasih. 🙏`;
                
                this.copyToClipboard(text);
            },

            copyToClipboard(text) {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(() => {
                        // Show toast or alert
                        alert('Teks berhasil disalin! Silakan tempel di WhatsApp.');
                        // Or open WhatsApp directly?
                        // window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
                    }).catch(err => {
                        console.error('Failed to copy: ', err);
                        this.fallbackCopyTextToClipboard(text);
                    });
                } else {
                    this.fallbackCopyTextToClipboard(text);
                }
            },
            
            fallbackCopyTextToClipboard(text) {
                var textArea = document.createElement("textarea");
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    var successful = document.execCommand('copy');
                    var msg = successful ? 'successful' : 'unsuccessful';
                    alert('Teks berhasil disalin! Silakan tempel di WhatsApp.');
                } catch (err) {
                    console.error('Fallback: Oops, unable to copy', err);
                    alert('Gagal menyalin teks.');
                }
                document.body.removeChild(textArea);
            }
        }
    }


</script>
