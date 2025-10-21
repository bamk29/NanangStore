<div x-data="posManager()" x-init="init()" @cart-updated.window="cartItemIds = $event.detail.items.map(item => item.id)" class="p-3 md:p-6 bg-gray-50 min-h-screen">
    <!-- Main Content -->
    <div class="flex-1 flex flex-col md:flex-row">
        <!-- Products Section -->
        <div class="flex-1 md:w-2/3 flex flex-col">
            <!-- Fixed Search Section -->
             <div class="bg-white p-4 rounded-lg shadow space-y-4 mb-4 mx-2 my-2">
                <input x-ref="searchInput" x-model.debounce.300ms="searchQuery" @keydown.enter="fetchProducts()" type="text"
                    class="border rounded-lg px-3 py-2 w-full text-sm sm:text-base" placeholder="Cari produk atau scan barcode...">

                <div x-data="{ showAll: false }">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <div class="flex-1 flex overflow-x-auto gap-2 pb-2 scrollbar-none">
                            <button @click="categoryId = ''"
                                class="flex-none px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors"
                                :class="{ 'bg-blue-500 text-white shadow-sm': categoryId === '' , 'bg-gray-100 text-gray-600 hover:bg-gray-200': categoryId !== '' }">
                                Semua
                            </button>
                            <template x-for="category in categories.slice(0, 5)" :key="category.id">
                                <button @click="categoryId = category.id" class="flex-none px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors"
                                :class="{ 'bg-blue-500 text-white shadow-sm': categoryId === category.id, 'bg-gray-100 text-gray-700 hover:bg-gray-200': categoryId !== category.id }">
                                    <span x-text="category.name"></span>
                                </button>
                            </template>
                        </div>
                        <template x-if="categories.length > 5">
                            <button @click="showAll = !showAll" class="flex-none flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 hover:bg-gray-200">
                                <span>Lainnya</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showAll }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                        </template>
                    </div>
                    <div x-show="showAll" x-collapse class="mt-3 pt-3 border-t">
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-2">
                            <template x-for="category in categories.slice(5)" :key="category.id">
                                <button @click="categoryId = category.id" class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                                :class="{ 'bg-blue-500 text-white': categoryId === category.id, 'bg-gray-100 text-gray-700 hover:bg-gray-200': categoryId !== category.id }">
                                    <span x-text="category.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scrollable Products Grid -->
            <div class="flex-1 overflow-y-auto bg-gray-50 px-2 pt-2 pb-safe relative">
                <div x-show="isLoading" class="absolute inset-0 bg-white/70 flex items-center justify-center z-10">
                    <p class="text-gray-500">Memuat produk...</p>
                </div>
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 auto-rows-fr">
                    <template x-for="(product, index) in products" :key="product.id">
                        <div @click="openModal(product)"
                            :id="'product-' + index"
                            class="relative bg-white rounded-lg shadow-sm hover:shadow-md active:scale-95 transition-all duration-150 cursor-pointer border"
                            :class="{
                                'border-red-500 border-4 shadow-lg': cartItemIds.includes(product.id),
                                'ring-2 ring-red-500 ring-offset-1': index === selectedIndex
                            }">

                            <button @click.stop="quickAddToCart(product)" x-show="!cartItemIds.includes(product.id)" class="absolute top-1 right-1 z-10 w-8 h-8 bg-blue-500 text-white rounded-full hover:bg-blue-900 active:scale-90 transition-transform flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            </button>

                            <div class="p-2 flex flex-col h-full">
                                <div class="mb-1">
                                    <h3 class="text-sm font-semibold text-gray-800 leading-tight line-clamp-2" x-text="product.name"></h3>
                                    <div class="text-xs text-gray-500" x-text="product.code"></div>
                                </div>
                                <div class="mt-auto">
                                    <div class="flex items-center justify-between">
                                        <div class="text-base font-bold text-blue-600" x-text="formatCurrency(product.retail_price)"></div>
                                        <div class="px-1.5 py-0.5 text-xs font-medium rounded-lg"
                                            :class="{
                                                'bg-green-100 text-green-700': product.stock > 5,
                                                'bg-yellow-100 text-yellow-700': product.stock > 0 && product.stock <= 5,
                                                'bg-red-100 text-red-700': product.stock <= 0
                                            }">
                                            <span x-text="'Stok: ' + product.stock"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <template x-if="!isLoading && products.length === 0">
                    <div class="text-center py-12">
                        <p class="text-gray-500">Produk tidak ditemukan.</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Cart Section -->
      <div
    class="w-full md:w-1/3 bg-white flex flex-col border  rounded-2xl shadow-2xl border-gray-200 mx-auto md:mx-3 mt-4 md:mt-0 z-50 ">
            <!-- Cart Header -->
            <div class="p-3 bg-white border-b border-gray-200 flex items-center justify-between rounded-t-lg">
                <div>
                    <h2 class="font-bold text-lg text-gray-800">Keranjang</h2>

                </div>
                <button wire:click="$dispatch('open-customer-modal')" class="flex items-center gap-2 px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-sm font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>Pilih Pelanggan</span>
                </button>
            </div>

            <!-- Cart Content -->
            <div class="flex-1 border-spacing-1 bg-gray-100">
                <livewire:pos.cart />
            </div>
        </div>
    </div>

    <!-- Floating Notification -->
    <div x-data="{ show: false, message: '', type: 'success' }"
        x-on:show-alert.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => { show = false }, 3000)"
        x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        class="fixed bottom-safe inset-x-0 mx-auto max-w-sm px-4 py-3 rounded-t-xl text-white text-center font-medium shadow-lg"
        :class="{ 'bg-green-500': type === 'success', 'bg-red-500': type === 'error' }" style="display: none;">
        <p x-text="message"></p>
    </div>

    <!-- Quantity Modal -->
    <div x-show="isModalOpen" x-cloak class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div @click.away="closeModal()" class="bg-white rounded-lg shadow-xl p-5 md:p-6 w-full max-w-md mx-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900" x-text="productForModal ? productForModal.name : ''"></h3>
                <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <div class="mb-6">
                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Kuantitas</label>
                <div class="flex items-center rounded-md shadow-sm">
                    <button type="button" @click="decrement()"  class="w-8 h-8 flex items-center justify-center mx-2 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 active:scale-95 transition-all duration-150 focus:outline-none">-</button>
                    <input type="number" step="0.01" id="quantity" name="quantity" x-ref="quantityInput" x-model="quantity" @input.debounce.300ms="validate()" @keydown.enter.prevent.stop="if(isQuantityValid) addToCartFromModal()" class="block w-full text-center border-gray-300 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <button type="button" @click="increment()"  class="w-8 h-8 flex items-center justify-center mx-2 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 active:scale-95 transition-all duration-150 focus:outline-none">+</button>
                </div>
                <p class="text-xs text-gray-500 mt-2" x-text="productForModal ? 'Stok tersedia: ' + productForModal.stock : ''"></p>
                <p x-show="!isQuantityValid" class="text-sm text-red-600 mt-2" x-text="errorMessage"></p>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">Batal</button>
                <button type="button" @click="addToCartFromModal()" :disabled="!isQuantityValid" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">Masukan ke Keranjang</button>
            </div>
        </div>
    </div>
</div>

<script>
    function posManager() {
        return {
            // Product Search State
            products: [],
            categories: [],
            searchQuery: '',
            categoryId: '',
            isLoading: true,
            selectedIndex: -1, // -1 means no selection

            // Cart State
            cartItemIds: [],

            // Quantity Modal State
            isModalOpen: false,
            productForModal: null,
            quantity: 1,
            isQuantityValid: true,
            errorMessage: '',

            // Init
            init() {
                this.fetchCategories();
                this.fetchProducts();
                this.initKeyboardListeners();

                this.$watch('searchQuery', (newValue, oldValue) => {
                    if (newValue !== oldValue) {
                        this.selectedIndex = -1; // Reset selection on new search
                        this.fetchProducts();
                    }
                });
                this.$watch('categoryId', () => {
                    this.selectedIndex = -1; // Reset selection on new category
                    this.fetchProducts();
                });
            },

            resetSearchAndFocus() {
                this.searchQuery = '';
                this.selectedIndex = -1;
                if (window.innerWidth > 768) {
                    this.$refs.searchInput.focus();
                }
            },

            // Keyboard Listeners
            initKeyboardListeners() {
                let barcode = '';
                let lastKeystrokeTime = 0;
                let processingBarcode = false;
                let lastSpaceTime = 0; // For double space shortcut

                window.addEventListener('keydown', (e) => {
                    // If modal is open, let it handle its own keyboard events
                    if (this.isModalOpen) {
                        return;
                    }

                    // Universal Reset Shortcuts
                    if (e.key === 'Escape') {
                        e.preventDefault();
                        this.resetSearchAndFocus();
                        return;
                    }

                    const targetIsSomeInput = e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable;
                    if (e.key === ' ' && !targetIsSomeInput) {
                        e.preventDefault();
                        const now = Date.now();
                        if (now - lastSpaceTime < 300) { // Double space pressed
                            this.resetSearchAndFocus();
                            lastSpaceTime = 0; // Reset timer
                        } else {
                            lastSpaceTime = now; // Record first space press
                        }
                        return;
                    }

                    // If in navigation mode, handle navigation
                    if (this.selectedIndex > -1) {
                        e.preventDefault();
                        switch (e.key) {
                            case 'ArrowDown':
                            case 'ArrowRight':
                                this.selectedIndex = Math.min(this.products.length - 1, this.selectedIndex + 1);
                                this.scrollIntoView();
                                break;
                            case 'ArrowUp':
                            case 'ArrowLeft':
                                this.selectedIndex = Math.max(0, this.selectedIndex - 1);
                                this.scrollIntoView();
                                break;
                            case 'Enter':
                                if (this.products[this.selectedIndex]) {
                                    this.openModal(this.products[this.selectedIndex]);
                                }
                                break;
                        }
                        return; // Stop further processing
                    }

                    // If not in navigation mode, check for entry points
                    // 1. Enter navigation from search input
                    if (e.target === this.$refs.searchInput && e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (this.products.length > 0) {
                            this.selectedIndex = 0;
                            this.scrollIntoView();
                        }
                        return;
                    }

                    // 2. Handle barcode scanning
                    const targetIsOurSearch = e.target === this.$refs.searchInput;

                    if (targetIsSomeInput && !targetIsOurSearch) {
                        return; // Ignore if other inputs are focused
                    }

                    if (e.key === 'Enter') {
                        const codeToProcess = targetIsOurSearch ? this.searchQuery : barcode;
                        if (codeToProcess.length > 2) {
                            e.preventDefault();
                            if(processingBarcode) return;
                            processingBarcode = true;
                            this.fetchProductsByBarcode(codeToProcess).finally(() => {
                                processingBarcode = false;
                                barcode = '';
                            });
                        }
                        return;
                    }

                    if (e.key.length > 1) return;

                    if (!targetIsSomeInput) {
                        const now = Date.now();
                        if (now - lastKeystrokeTime > 100) {
                            barcode = '';
                        }
                        barcode += e.key;
                        lastKeystrokeTime = now;
                    }
                });
            },

            scrollIntoView() {
                this.$nextTick(() => {
                    const el = document.getElementById('product-' + this.selectedIndex);
                    if (el) {
                        el.scrollIntoView({ block: 'nearest', inline: 'nearest' });
                    }
                });
            },

            async fetchProductsByBarcode(barcode) {
                this.isLoading = true;
                this.searchQuery = barcode;

                try {
                    const params = new URLSearchParams({ q: barcode, category_id: '' });
                    const response = await fetch(`/api/products?${params}`);
                    const data = await response.json();

                    if (data.length === 1) {
                        this.quickAddToCart(data[0]);
                        this.searchQuery = '';
                        await this.fetchProducts();
                    } else {
                        this.products = data;
                    }
                } catch (error) {
                    console.error('Error fetching products by barcode:', error);
                } finally {
                    this.isLoading = false;
                    if (window.innerWidth > 768) {
                        this.$refs.searchInput.focus();
                    }
                }
            },

            // Product Search Methods
            fetchCategories() {
                fetch('/api/categories')
                    .then(response => response.json())
                    .then(data => { this.categories = data; });
            },
            fetchProducts() {
                return new Promise((resolve, reject) => {
                    this.isLoading = true;
                    const params = new URLSearchParams({ q: this.searchQuery, category_id: this.categoryId });
                    fetch(`/api/products?${params}`)
                        .then(response => response.json())
                        .then(data => {
                            this.products = data;
                            this.isLoading = false;
                            resolve();
                        })
                        .catch(error => {
                            console.error('Error fetching products:', error);
                            this.isLoading = false;
                            reject(error);
                        });
                });
            },

            // Cart Methods
            quickAddToCart(product) {
                if (product.stock <= 0) {
                    window.Livewire.dispatch('show-alert', { type: 'error', message: 'Stok produk habis.' });
                    return;
                }
                this.$dispatch('add-to-cart', {
                    product: product,
                    quantity: 1
                });
            },

            // Quantity Modal Methods
            openModal(product) {
                if (product.stock <= 0) {
                    window.Livewire.dispatch('show-alert', { type: 'error', message: 'Stok produk habis.' });
                    return;
                }
                this.productForModal = product;
                this.quantity = 1;
                this.isQuantityValid = true;
                this.errorMessage = '';
                this.isModalOpen = true;
                this.$nextTick(() => {
                    this.$refs.quantityInput.focus();
                    this.$refs.quantityInput.select();
                });
            },
            closeModal() {
                document.activeElement.blur();
                this.isModalOpen = false;
                this.productForModal = null;
                this.selectedIndex = -1; // Exit navigation mode
                this.searchQuery = ''; // Clear search query

                if (window.innerWidth > 768) {
                    this.$nextTick(() => this.$refs.searchInput.focus());
                }
            },
            increment() {
                let currentQuantity = parseFloat(this.quantity) || 0;
                this.quantity = currentQuantity + 1;
                this.validate();
            },
            decrement() {
                let currentQuantity = parseFloat(this.quantity) || 0;
                if (currentQuantity > 1) {
                    this.quantity = currentQuantity - 1;
                }
                this.validate();
            },
            validate() {
                let qty = parseFloat(this.quantity);
                if (isNaN(qty) || qty <= 0) {
                    this.isQuantityValid = false;
                    this.errorMessage = 'Kuantitas harus lebih dari 0.';
                    return;
                }
                if (qty > this.productForModal.stock) {
                    this.isQuantityValid = false;
                    this.errorMessage = 'Kuantitas melebihi stok yang tersedia.';
                    return;
                }
                this.isQuantityValid = true;
                this.errorMessage = '';
            },
            addToCartFromModal() {
                this.validate();
                if (!this.isQuantityValid) return;

                this.$dispatch('add-to-cart', {
                    product: this.productForModal,
                    quantity: this.quantity
                });
                this.closeModal();
            },

            // Helper
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
