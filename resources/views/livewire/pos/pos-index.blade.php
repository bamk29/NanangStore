<div x-data="posManager()"
     x-init="init()"
     @cart-updated.window="cartItemIds = $event.detail.items.map(item => item.id)"
     @cart:reset.window="if (!isDesktop) isCartVisible = false"
     @cart:reset.window="if (!isDesktop) isCartVisible = false"
     @pending-transaction-loaded.window="if (!isDesktop) isCartVisible = true"
     @customer:selected.window="setCustomer($event.detail.customer)"
     @customer:cleared.window="clearCustomer()"
     @keydown.escape.window="handleEscape()"
     class="h-screen flex flex-col md:flex-row bg-gray-50 overflow-hidden">

    <!-- Left Column (Products & Search) -->
    <div class="flex-1 flex flex-col">
        <!-- Top Bar -->
        <div class="flex-shrink-0 bg-white p-2 space-y-2 z-10 lg:shadow">
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input x-ref="searchInput" x-model.debounce.300ms="searchQuery" @keydown.enter="fetchProducts()" type="text"
                        :readonly="isScannerMode"
                        :class="{ 'bg-gray-100 focus:bg-gray-100 cursor-not-allowed': isScannerMode, 'bg-white': !isScannerMode }"
                        class="border rounded-lg px-3 py-2 w-full text-sm sm:text-base pr-32" placeholder="Cari produk atau scan barcode...">

                    <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                        <button @click="searchQuery = ''"
                                x-show="searchQuery.length > 0 && !isScannerMode"
                                x-transition:enter="transition-opacity ease-out duration-200"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition-opacity ease-in duration-150"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="px-2 py-1 border border-zinc-800 bg-zinc-100 rounded-md text-sm font-medium text-zinc-700 hover:text-zinc-900 hover:bg-zinc-20 mr-2"
                                style="display: none;">
                            Clear
                        </button>
                        <span x-show="isScannerMode" x-transition class="text-xs text-blue-600 font-semibold mr-2">Mode Scanner</span>
                        <button @click="isScannerMode = !isScannerMode"
                                class="p-2 rounded-lg transition-colors"
                                :class="isScannerMode ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                title="Toggle Mode Scanner (F3)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/>
                                <path stroke-linecap="round" d="M9 10v4M12 10v4M15 10v4"/>
                                <path stroke-linecap="round" d="M7 18h10"/>
                              </svg>
                        </button>
                    </div>
                </div>
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
                        <button x-show="isCustomerSelected" @click="categoryId = 'recommendations'"
                            class="flex-none px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors"
                            :class="{ 'bg-purple-500 text-white shadow-sm': categoryId === 'recommendations', 'bg-purple-100 text-purple-700 hover:bg-purple-200': categoryId !== 'recommendations' }"
                            style="display: none;">
                            ★ Sering Dibeli
                        </button>
                        <template x-for="category in categories.slice(0, 2)" :key="category.id">
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
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-4 lg:grid-cols-6 gap-2">
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

            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-6 gap-2 auto-rows-fr">
                <template x-for="(product, index) in products" :key="product.id">
                    <div @click="openModal(product)"
                        :id="'product-' + index"
                        class="relative bg-white rounded-lg shadow-2xl hover:shadow-xl hover:-translate-y-1 active:scale-95 transition-all duration-150 cursor-pointer border"
                        :class="{
                            'border-blue-500 border-4': cartItemIds.includes(product.id),
                            'ring-2 ring-red-500 ring-offset-1': index === selectedIndex
                        }">

                        <button @click.stop="quickAddToCart(product)" x-show="!cartItemIds.includes(product.id)" class="absolute top-1.5 right-1.5 z-10 w-10 h-10 bg-white/70 backdrop-blur-sm border-2 border-blue-500 text-blue-500 rounded-full hover:bg-blue-500 hover:text-white active:scale-90 transition-all flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        </button>

                        <div class="p-1.5 flex flex-col h-full">
                            <div class="mb-1 flex-grow pr-8">
                                <h3 class="text-sm font-bold text-gray-800 leading-tight line-clamp-2" x-text="product.name"></h3>
                                <div class="text-xs text-gray-500" x-text="product.code"></div>
                            </div>
                            <div class="mt-auto">
                                <div class="text-sm font-bold text-blue-600" x-text="formatCurrency(product.retail_price)"></div>
                                <div class="px-1.5 py-0.5 text-xs font-medium rounded-lg mt-1"
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
    <div class="hidden lg:flex lg:w-1/3 xl:w-1/3 flex-col h-full border-l bg-white">
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
        <div @click.away="closeModal()" class="bg-white rounded-2xl shadow-xl p-5 md:p-6 w-full max-w-sm mx-auto">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-bold text-gray-900 pr-4" x-text="productForModal ? productForModal.name : ''"></h3>
                <button @click="closeModal()" class="-mt-2 -mr-2 p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="mb-6 text-center">
                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Kuantitas</label>
                <div class="flex items-center justify-center rounded-md">
                    <button type="button" @click="decrement()"  class="w-12 h-12 flex items-center justify-center text-2xl bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 active:scale-95 transition-all duration-150 focus:outline-none">-</button>
                    <input type="number" step="1" id="quantity" name="quantity" x-ref="quantityInput" x-model="quantity" @input.debounce.300ms="validate()" @keydown.enter.prevent.stop="if(isQuantityValid) addToCartFromModal()" class="block w-24 mx-4 text-center text-2xl font-bold border-gray-300 focus:ring-blue-500 focus:border-blue-500 rounded-lg">
                    <button type="button" @click="increment()"  class="w-12 h-12 flex items-center justify-center text-2xl bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 active:scale-95 transition-all duration-150 focus:outline-none">+</button>
                </div>
                <p class="text-xs text-gray-500 mt-2" x-text="productForModal ? 'Stok tersedia: ' + productForModal.stock : ''"></p>
                <p x-show="!isQuantityValid" class="text-sm text-red-600 mt-2" x-text="errorMessage"></p>
            </div>

            <div class="grid grid-cols-2 gap-3 mt-8">
                <button type="button" @click="closeModal()" class="w-full px-4 py-3 text-sm font-bold text-gray-700 bg-gray-200 rounded-lg shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">Batal</button>
                <button type="button" @click="addToCartFromModal()" :disabled="!isQuantityValid" class="w-full inline-flex justify-center px-4 py-3 text-sm font-bold text-white bg-blue-600 border border-transparent rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">Tambah</button>
            </div>
        </div>
    </div>
</div>

<script>
    function posManager() {
        return {
            // UI State
            isDesktop: window.innerWidth >= 768,
            isCartVisible: window.innerWidth >= 768,
            isLandscape: window.matchMedia("(orientation: landscape)").matches,
            isScannerMode: false,

            // Product Search State
            products: [],
            categories: [],
            searchQuery: '',
            categoryId: '',
            isLoading: true,
            selectedIndex: -1, // -1 means no selection
            selectedIndex: -1, // -1 means no selection
            ignoreNextSearchQueryWatch: false,
            recommendedProducts: [],
            isCustomerSelected: false,
            currentCustomerId: null,

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
                    this.isDesktop = window.innerWidth >= 768;
                    this.isLandscape = window.matchMedia("(orientation: landscape)").matches;
                    if (this.isDesktop) {
                        this.isCartVisible = true;
                    } else {
                        this.isCartVisible = false;
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
                    // If scanner mode is on, do nothing as the scanner "types"
                    if (this.isScannerMode) {
                        return;
                    }
                    // Otherwise, filter products as a normal user types
                    this.selectedIndex = -1; // Reset selection on new search
                    this.categoryId = ''; // Reset category filter
                    this.fetchProducts();
                });
                this.$watch('categoryId', (value) => {
                    this.selectedIndex = -1; // Reset selection on new category
                    this.searchQuery = ''; // Reset search query
                    if (value === 'recommendations') {
                        this.products = this.recommendedProducts;
                    } else {
                        this.fetchProducts();
                    }
                });
            },

            setCustomer(customer) {
                this.isCustomerSelected = true;
                this.currentCustomerId = customer.id;
                this.fetchRecommendations(customer.id);
            },

            clearCustomer() {
                this.isCustomerSelected = false;
                this.currentCustomerId = null;
                this.recommendedProducts = [];
                if (this.categoryId === 'recommendations') {
                    this.categoryId = '';
                }
            },

            fetchRecommendations(customerId) {
                fetch(`/api/products/recommendations/${customerId}`)
                    .then(response => response.json())
                    .then(data => {
                        this.recommendedProducts = data;
                        // Optional: Auto-switch to recommendations if found
                        // if (data.length > 0) this.categoryId = 'recommendations';
                    })
                    .catch(error => console.error('Error fetching recommendations:', error));
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

                let barcodeBuffer = '';
                let lastKeystrokeTime = 0;
                let lastSpaceTime = 0; 
                
                // Scanner State
                this.scanQueue = [];
                this.isProcessingQueue = false;
                this.lastScannedBarcode = '';
                this.lastScanTime = 0;

                window.addEventListener('keydown', (e) => {
                    // Shortcut global
                    if (e.key === 'F2') { e.preventDefault(); window.dispatchEvent(new CustomEvent('shortcut:pay')); return; }
                    if (e.key === 'F3') { e.preventDefault(); this.isScannerMode = !this.isScannerMode; return; }
                    if (e.key === 'F4') { e.preventDefault(); window.dispatchEvent(new CustomEvent('shortcut:hold')); return; }
                    if (e.key === 'Escape') { this.handleEscape(); return; }

                    // Ignore if modal/cart open (except Escape)
                    if (this.isModalOpen || (!this.isDesktop && this.isCartVisible)) return;

                    const targetIsInput = e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable;
                    const targetIsSearch = e.target === this.$refs.searchInput;

                    // Double space shortcut
                    if (e.key === ' ' && !targetIsInput) {
                        e.preventDefault();
                        const now = Date.now();
                        if (now - lastSpaceTime < 300) { 
                            this.resetSearchAndFocus(); 
                            lastSpaceTime = 0; 
                        } else { 
                            lastSpaceTime = now; 
                        }
                        return;
                    }

                    // Navigation
                    if (this.selectedIndex > -1) {
                        // ... navigation logic (keep existing) ...
                        if(['ArrowDown','ArrowRight','ArrowUp','ArrowLeft','Enter'].includes(e.key)) {
                            e.preventDefault();
                            if(e.key === 'ArrowDown' || e.key === 'ArrowRight') { this.selectedIndex = Math.min(this.products.length - 1, this.selectedIndex + 1); this.scrollIntoView(); }
                            if(e.key === 'ArrowUp' || e.key === 'ArrowLeft') { this.selectedIndex = Math.max(0, this.selectedIndex - 1); this.scrollIntoView(); }
                            if(e.key === 'Enter' && this.products[this.selectedIndex]) { this.openModal(this.products[this.selectedIndex]); }
                            return;
                        }
                    }

                    // Enter navigation from search
                    if (targetIsSearch && e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (this.products.length > 0) { this.selectedIndex = 0; this.scrollIntoView(); }
                        return;
                    }

                    // --- BARCODE SCANNER LOGIC ---
                    
                    // 1. Handle "Enter" (End of scan)
                    if (e.key === 'Enter') {
                        // Determine what to process: buffer or search input
                        // If the buffer has content, it takes precedence (scanner usually types fast then hits enter)
                        // If buffer is empty, check search input
                        
                        let codeToProcess = '';
                        
                        if (barcodeBuffer.length > 2) {
                            codeToProcess = barcodeBuffer;
                            e.preventDefault(); // Prevent form submission or newline
                        } else if (targetIsSearch && this.searchQuery.length > 2) {
                            codeToProcess = this.searchQuery;
                            // Don't prevent default here if you want normal enter behavior, 
                            // but for a POS search box, enter usually means "search" or "pick first".
                            // Let's treat it as a potential barcode if it looks like one, or just search.
                            // For now, let's assume if they hit enter in search, they might be manually typing a barcode.
                        }

                        if (codeToProcess) {
                            this.handleScannedCode(codeToProcess);
                            barcodeBuffer = ''; // Clear buffer
                            if (targetIsSearch) {
                                this.searchQuery = ''; // Clear search input if it was used
                                this.ignoreNextSearchQueryWatch = true; // Prevent search trigger
                                this.$refs.searchInput.blur(); // Remove focus to prevent interference
                            }
                        }
                        return;
                    }

                    // 2. Capture Keystrokes
                    // Ignore special keys
                    if (e.key.length > 1) return;

                    // Timing check: Scanners type VERY fast (usually < 50ms between keys)
                    // Humans type slower.
                    const now = Date.now();
                    
                    if (now - lastKeystrokeTime > 100) {
                        // Gap too long, reset buffer (assume new scan or manual typing started)
                        barcodeBuffer = ''; 
                    }
                    
                    // If focusing another input (not search), don't capture into buffer unless it looks like a scan
                    if (targetIsInput && !targetIsSearch) {
                        // If it's a fast burst, maybe it IS a scanner even in another input?
                        // But usually we don't want to interfere with typing notes.
                        // So we only capture if NOT in another input, OR if it's the search input.
                        lastKeystrokeTime = now;
                        return; 
                    }

                    barcodeBuffer += e.key;
                    lastKeystrokeTime = now;
                });

                window.posKeyboardListenerAttached = true;
            },

            handleScannedCode(code) {
                const now = Date.now();
                
                // Duplicate Scan Prevention (0.5s delay for SAME item)
                if (code === this.lastScannedBarcode && (now - this.lastScanTime < 500)) {
                    console.log('Duplicate scan ignored (debounce)');
                    return;
                }

                this.lastScannedBarcode = code;
                this.lastScanTime = now;

                // Add to queue
                this.scanQueue.push(code);
                this.processScanQueue();
            },

            async processScanQueue() {
                if (this.isProcessingQueue || this.scanQueue.length === 0) return;

                this.isProcessingQueue = true;
                const code = this.scanQueue.shift(); // Get first item

                try {
                    await this.fetchProductsByBarcode(code);
                } catch (err) {
                    console.error("Scan error:", err);
                } finally {
                    this.isProcessingQueue = false;
                    // Process next item immediately if exists
                    if (this.scanQueue.length > 0) {
                        this.processScanQueue();
                    }
                }
            },

            async fetchProductsByBarcode(barcode) {
                try {
                    // Optimistic UI: Play sound immediately? No, wait for result to know if success/error.
                    // But we want it "fast".
                    
                    const response = await fetch(`/api/products/by-code/${barcode}`);
                    const product = await response.json();

                    if (response.ok) {
                        this.quickAddToCart(product);
                        // Sound is played in quickAddToCart
                        
                        // Clear search if it was populated
                        if (this.searchQuery === barcode) {
                            this.searchQuery = '';
                            this.ignoreNextSearchQueryWatch = true;
                        }
                    } else {
                        this.playSound('error');
                        window.Livewire.dispatch('show-alert', { type: 'error', message: product.message || 'Produk tidak ditemukan.' });
                    }
                } catch (error) {
                    console.error('Error fetching product by barcode:', error);
                    this.playSound('error');
                    window.Livewire.dispatch('show-alert', { type: 'error', message: 'Gagal mengambil data produk.' });
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
                            this.products = data.slice(0, 40);
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
                let newQuantity = currentQuantity + 1;
                // Round to 2 decimal places to avoid floating point issues
                this.quantity = Math.round((newQuantity + Number.EPSILON) * 100) / 100;
                this.validate();
            },
            decrement() {
                let currentQuantity = parseFloat(this.quantity) || 0;
                let newQuantity = currentQuantity - 1;
                if (newQuantity < 1) { // Changed from 0.01 to 1
                    this.quantity = 0;
                } else {
                    // Round to 2 decimal places
                    this.quantity = Math.round((newQuantity + Number.EPSILON) * 100) / 100;
                }
                this.validate();
            },
            validate() {
                if (!this.productForModal) {
                    this.isQuantityValid = false;
                    return;
                }
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
