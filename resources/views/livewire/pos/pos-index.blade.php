<div x-data="posManager()"
     x-init="init()"
     @cart-updated.window="cartItemIds = $event.detail.items.map(item => item.id)"
     @cart:reset.window="if (!isDesktop) isCartVisible = false"
     @pending-transaction-loaded.window="if (!isDesktop) isCartVisible = true"
     @keydown.escape.window="handleEscape()"
     class="h-screen flex flex-col lg:flex-row bg-gray-50 overflow-hidden">

    <!-- Left Column (Products & Search) -->
    <div class="flex-1 flex flex-col">
        <!-- Top Bar -->
        <div class="flex-shrink-0 bg-white p-2 space-y-2 z-10 lg:shadow">
            <div class="flex items-center gap-2">
                <input x-ref="searchInput" x-model.debounce.300ms="searchQuery" @keydown.enter="fetchProducts()" type="text"
                    class="border rounded-lg px-3 py-2 w-full text-sm sm:text-base" placeholder="Cari produk atau scan barcode...">
                <button @click="$store.ui.isBottomNavVisible = !$store.ui.isBottomNavVisible" class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                </button>
            </div>

            <div x-data="{ showAll: false }">
                <div class="flex items-center gap-2">
                    <div class="flex-1 flex overflow-x-auto gap-2 pb-2 scrollbar-none">
                        <button @click="categoryId = ''"
                            class="flex-none px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors"
                            :class="{ 'bg-blue-500 text-white shadow-sm': categoryId === '' , 'bg-gray-100 text-gray-600 hover:bg-gray-200': categoryId !== '' }">
                            Semua
                        </button>
                        <template x-for="category in categories.slice(0, 4)" :key="category.id">
                            <button @click="categoryId = category.id" class="flex-none px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors"
                            :class="{ 'bg-blue-500 text-white shadow-sm': categoryId === category.id, 'bg-gray-100 text-gray-700 hover:bg-gray-200': categoryId !== category.id }">
                                <span x-text="category.name"></span>
                            </button>
                        </template>
                    </div>
                    <template x-if="categories.length > 4">
                        <button @click="showAll = !showAll" class="flex-none flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 hover:bg-gray-200">
                            <span>Lainnya</span>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showAll }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </template>
                </div>
                <div x-show="showAll" x-collapse class="mt-3 pt-3 border-t">
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-2">
                        <template x-for="category in categories" :key="category.id">
                            <button @click="categoryId = category.id; showAll = false;" class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                            :class="{ 'bg-blue-500 text-white': categoryId === category.id, 'bg-gray-100 text-gray-700 hover:bg-gray-200': categoryId !== category.id }">
                                <span x-text="category.name"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="flex-1 flex flex-col overflow-y-auto p-2 sm:p-4 relative">
            <!-- Loading Overlay -->
            <div x-show="isLoading"
                 x-transition:enter="transition-opacity ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-in duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-10 rounded-lg" x-cloak>
                <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-5 xl:grid-cols-6 gap-2 auto-rows-fr">
                <template x-for="(product, index) in products" :key="product.id">
                    <div @click="openModal(product)"
                        :id="'product-' + index"
                        class="relative bg-white rounded-lg shadow-sm hover:shadow-md active:scale-95 transition-all duration-150 cursor-pointer border"
                        :class="{
                            'border-blue-500 border-2': cartItemIds.includes(product.id),
                            'ring-2 ring-red-500 ring-offset-1': index === selectedIndex
                        }">

                        <button @click.stop="quickAddToCart(product)" x-show="!cartItemIds.includes(product.id)" class="absolute top-1 right-1 z-10 w-8 h-8 bg-blue-500 text-white rounded-full hover:bg-blue-700 active:scale-90 transition-transform flex items-center justify-center shadow">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        </button>

                        <div class="p-1.5 flex flex-col h-full">
                            <div class="mb-1 flex-grow">
                                <h3 class="text-xs font-semibold text-gray-800 leading-tight line-clamp-2" x-text="product.name"></h3>
                                <div class="text-xs text-gray-500" x-text="product.code"></div>
                            </div>
                            <div class="mt-auto">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-bold text-blue-600" x-text="formatCurrency(product.retail_price)"></div>
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
                <div class="text-center py-12 flex-1 flex items-center justify-center">
                    <p class="text-gray-500">Produk tidak ditemukan.</p>
                </div>
            </template>
        </div>
    </div>

    <!-- Cart Section (Desktop) -->
    <div class="hidden lg:flex lg:w-1/3 xl:w-1/4 flex-col border-l bg-white">
        <livewire:pos.cart />
    </div>

    <!-- Mobile Cart -->
    <div x-show="!isDesktop" x-cloak>
        <!-- Backdrop -->
        <div x-show="isCartVisible" @click="isCartVisible = false"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/40 z-30" x-cloak>
        </div>

        <!-- Floating Action Button (FAB) -->
        <div x-show="!isCartVisible" class="fixed bottom-6 right-6 z-20">
            <button @click="isCartVisible = true" class="relative bg-blue-600 text-white rounded-full h-16 w-16 flex items-center justify-center shadow-lg hover:bg-blue-700 active:scale-95 transition-transform">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span x-show="cartItemIds.length > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center" x-text="cartItemIds.length"></span>
            </button>
        </div>

        <!-- Cart Bottom Sheet -->
        <div x-show="isCartVisible"
             class="fixed bottom-0 left-0 right-0 z-40 flex flex-col bg-white rounded-t-2xl shadow-[0_-10px_25px_-5px_rgba(0,0,0,0.1)]"
             :style="isLandscape ? 'height: 65vh;' : 'height: 85vh;'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="transform translate-y-full"
             x-transition:enter-end="transform translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="transform translate-y-0"
             x-transition:leave-end="transform translate-y-full">
            <div class="p-3 bg-white border-b border-gray-200 flex items-center justify-between rounded-t-2xl">
                <h2 class="font-bold text-lg text-gray-800">Keranjang</h2>
                <button @click="isCartVisible = false" class="p-2 rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="flex-1 bg-gray-100 min-h-0">
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
        class="fixed bottom-safe inset-x-0 mx-auto max-w-sm px-4 py-3 rounded-t-xl text-white text-center font-medium shadow-lg z-50"
        :class="{ 'bg-green-500': type === 'success', 'bg-red-500': type === 'error' }" style="display: none;">
        <p x-text="message"></p>
    </div>

    <!-- Quantity Modal -->
    <div x-show="isModalOpen" x-cloak class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-[60] p-4">
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
            // UI State
            isDesktop: window.innerWidth >= 1024,
            isCartVisible: window.innerWidth >= 1024,
            isLandscape: window.matchMedia("(orientation: landscape)").matches,

            // Product Search State
            products: [],
            categories: [],
            searchQuery: '',
            categoryId: '',
            isLoading: true,
            selectedIndex: -1, // -1 means no selection
            ignoreNextSearchQueryWatch: false,

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

                // Set initial state of main navigation
                this.$store.ui.isBottomNavVisible = false;

                const handleResize = () => {
                    this.isDesktop = window.innerWidth >= 1024;
                    this.isLandscape = window.matchMedia("(orientation: landscape)").matches;
                    if(this.isDesktop) {
                        this.isCartVisible = true;
                    }
                };
                window.addEventListener('resize', handleResize);

                // Check URL on init to auto-open cart for resume/correction on mobile
                const urlParams = new URLSearchParams(window.location.search);
                if ((urlParams.has('resume') || urlParams.has('correct')) && !this.isDesktop) {
                    // Use a small timeout to ensure cart items are rendered before animation
                    setTimeout(() => {
                        this.isCartVisible = true;
                    }, 100);
                }

                // When leaving the page, restore the bottom nav
                window.addEventListener('beforeunload', () => {
                    this.$store.ui.isBottomNavVisible = true;
                });

                this.$watch('searchQuery', () => {
                    if (this.ignoreNextSearchQueryWatch) {
                        this.ignoreNextSearchQueryWatch = false;
                        return;
                    }
                    this.selectedIndex = -1; // Reset selection on new search
                    this.categoryId = ''; // Reset category filter
                    this.fetchProducts();
                });
                this.$watch('categoryId', () => {
                    this.selectedIndex = -1; // Reset selection on new category
                    this.searchQuery = ''; // Reset search query
                    this.fetchProducts();
                });
            },

            handleEscape() {
                if (this.isModalOpen) {
                    this.closeModal();
                } else {
                    this.clearSearchAndExit();
                }
            },

            clearSearchAndExit() {
                this.searchQuery = '';
                this.selectedIndex = -1;
                this.$refs.searchInput.blur();
            },

            resetSearchAndFocus() {
                this.searchQuery = '';
                this.selectedIndex = -1;
                if (this.isDesktop) {
                    this.$refs.searchInput.focus();
                }
            },

            // Keyboard Listeners
            initKeyboardListeners() {
                if (window.posKeyboardListenerAttached) {
                    return;
                }

                let barcode = '';
                let lastKeystrokeTime = 0;
                let processingBarcode = false;
                let lastSpaceTime = 0; // For double space shortcut

                window.addEventListener('keydown', (e) => {
                    // Shortcut global untuk Bayar (F2) dan Tunda (F4)
                    if (e.key === 'F2') {
                        e.preventDefault();
                        window.dispatchEvent(new CustomEvent('shortcut:pay'));
                    }
                    if (e.key === 'F4') {
                        e.preventDefault();
                        window.dispatchEvent(new CustomEvent('shortcut:hold'));
                    }

                    // If a modal is open, or the cart is open on mobile, ignore product navigation
                    if (this.isModalOpen || (!this.isDesktop && this.isCartVisible)) {
                        // Exception for Escape key, which is handled globally now
                        if (e.key === 'Escape') {
                            this.handleEscape();
                        }
                        return;
                    }

                    const targetIsSomeInput = e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable;
                    if (e.key === ' ' && !targetIsSomeInput) {
                        e.preventDefault();
                        const now = Date.now();
                        if (now - lastSpaceTime < 300) { // Double space pressed
                            this.resetSearchAndFocus(); // This one still focuses
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

                window.posKeyboardListenerAttached = true;
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
                // This function no longer interacts with the main search query or product list.
                // It directly finds a product and adds it to the cart.
                this.isLoading = true;
                try {
                    const response = await fetch(`/api/products/by-code/${barcode}`);
                    const product = await response.json();

                    if (response.ok) {
                        this.quickAddToCart(product);
                        this.playSound('success');
                        // Clear search and remove focus from the input
                        this.ignoreNextSearchQueryWatch = true;
                        this.clearSearchAndExit();
                    } else {
                        this.playSound('error');
                        window.Livewire.dispatch('show-alert', { type: 'error', message: product.message || 'Produk tidak ditemukan.' });
                    }
                } catch (error) {
                    console.error('Error fetching product by barcode:', error);
                    this.playSound('error');
                    window.Livewire.dispatch('show-alert', { type: 'error', message: 'Gagal mengambil data produk.' });
                } finally {
                    this.isLoading = false;
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
                    this.playSound('error');
                    window.Livewire.dispatch('show-alert', { type: 'error', message: 'Stok produk habis.' });
                    return;
                }
                this.playSound('success');
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

                if (this.isDesktop) {
                    this.$nextTick(() => this.$refs.searchInput.focus());
                }
            },
            increment() {
                let currentQuantity = parseFloat(this.quantity) || 0;
                let newQuantity = currentQuantity + 0.1;
                // Round to 2 decimal places to avoid floating point issues
                this.quantity = Math.round((newQuantity + Number.EPSILON) * 100) / 100;
                this.validate();
            },
            decrement() {
                let currentQuantity = parseFloat(this.quantity) || 0;
                let newQuantity = currentQuantity - 0.1;
                if (newQuantity < 0.01) { // Check against a small number to handle floating point inaccuracies
                    this.quantity = 0;
                } else {
                    // Round to 2 decimal places
                    this.quantity = Math.round((newQuantity + Number.EPSILON) * 100) / 100;
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

                this.playSound('success');
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
            },
            playSound(type) {
                const audio = new Audio(`/sounds/${type}.mp3`);
                audio.play().catch(e => console.error("Error playing sound:", e));
            }
        }
    }
</script>
