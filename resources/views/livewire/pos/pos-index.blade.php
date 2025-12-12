<div x-data="posManager()"
     x-init="init()"
     @cart-updated.window="cartItemIds = $event.detail.items.map(item => item.id); cartTotal = $event.detail.total || 0"
     @cart:reset.window="if (!isDesktop) isCartVisible = false"
     @cart:reset.window="if (!isDesktop) isCartVisible = false"
     @pending-transaction-loaded.window="if (!isDesktop) isCartVisible = true"
     @customer:selected.window="setCustomer($event.detail.customer)"
     @customer:cleared.window="clearCustomer()"
     @customer:selected.window="setCustomer($event.detail.customer)"
     @customer:cleared.window="clearCustomer()"
     @keydown.window="handleGlobalKeydown($event)"
     class="h-screen flex flex-col md:flex-row bg-gray-50 overflow-hidden">
     
    <!-- Camera Scanner Component -->
    <x-camera-scanner @scan-completed="handleScannedCode($event.detail)" />

    <!-- GHOST INPUT / TRAP INPUT FOR SCANNER MODE (F3) -->
    <!-- Positioned off-screen/hidden but active for focus -->
    <input type="text" 
           x-ref="scannerTrap"
           x-model="ghostQuery"
           class="fixed top-0 left-0 w-px h-px opacity-0 overflow-hidden -z-10"
           autocomplete="off"
           @keydown="handleTrapKeydown($event)"
           @blur="if(isScannerMode) $nextTick(() => $el.focus())"
    >

    <!-- Left Column (Products & Search) -->
    <div class="flex-1 flex flex-col">
        <!-- Top Bar -->
        <div class="flex-shrink-0 bg-white/80 backdrop-blur-md p-3 space-y-3 z-10 lg:shadow sticky top-0">
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input x-ref="searchInput" x-model.debounce.300ms="searchQuery" @keydown.enter="if(!isScanning) fetchProducts()" type="text"
                        :readonly="isScannerMode"
                        :class="{ 'bg-gray-100 focus:bg-gray-100 cursor-not-allowed': isScannerMode, 'bg-white focus:ring-4 focus:ring-blue-100': !isScannerMode && !isSearchFocusedByShortcut, 'ring-4 ring-yellow-300 focus:ring-yellow-300 transition-none': isSearchFocusedByShortcut }"
                        class="border-gray-300 rounded-xl px-4 py-2.5 w-full text-base shadow-sm focus:border-blue-500 transition-all duration-200 pr-32" placeholder="Cari produk atau scan barcode...">
                        
                    <!-- Search Shortcut Badge (/) -->
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" 
                         x-show="searchQuery.length === 0 && !isScannerMode">
                        <kbd class="hidden sm:inline-block px-2 py-0.5 text-xs font-semibold text-gray-400 bg-gray-50 border border-gray-200 rounded-md shadow-sm">/</kbd>
                    </div>
                    
                    <!-- Camera Button for POS (Merged) -->
                     <div class="absolute inset-y-0 right-0 flex items-center pr-2">


                        <button @click="resetSearchAndFocus()"
                                x-show="searchQuery.length > 0 && !isScannerMode"
                                x-transition:enter="transition-opacity ease-out duration-200"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition-opacity ease-in duration-150"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="px-2 py-1 border border-zinc-800 bg-zinc-100 rounded-md text-sm font-medium text-zinc-700 hover:text-zinc-900 hover:bg-zinc-20 mr-2 flex items-center gap-1">
                            Clear <kbd class="text-[10px] px-1 bg-white border border-gray-300 rounded text-gray-500">Del</kbd>
                        </button>
                        <span x-show="isScannerMode" x-transition class="text-xs text-blue-600 font-bold mr-2 animate-pulse">SCANNER AKTIF</span>
                        <button @click="isScannerMode = !isScannerMode"
                                class="p-2 rounded-lg transition-all duration-200"
                                :class="isScannerMode ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/30 ring-2 ring-blue-300 ring-offset-1' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                title="Toggle Mode Scanner (F3)">
                            <div class="relative">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/>
                                    <path stroke-linecap="round" d="M9 10v4M12 10v4M15 10v4"/>
                                    <path stroke-linecap="round" d="M7 18h10"/>
                                </svg>
                                <span class="absolute -bottom-3 -right-3 px-1 py-0.5 text-[9px] font-bold bg-gray-800 text-white rounded border border-gray-600 shadow-sm" x-show="!isScannerMode">F3</span>
                            </div>
                        </button>
                    </div>
                </div>
                <button @click="$dispatch('open-camera-scanner')" class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 lg:hidden" title="Scan Kamera">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </button>
                <button @click="showShortcutModal = true" class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600" title="Shortcut Keyboard (?)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                </button>
                <button @click="$store.ui.isBottomNavVisible = !$store.ui.isBottomNavVisible" class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                </button>
            </div>

            <div x-data="{ showAll: false }">
                <div class="flex items-center gap-2">
                    <div class="flex-1 flex overflow-x-auto gap-2 pb-2 scrollbar-none">
                        <!-- Semua -->
                        <button @click="categoryId = ''"
                            class="flex-none px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all duration-200 active:scale-95"
                            :class="{ 'bg-slate-800 text-white shadow-md': categoryId === '' , 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300': categoryId !== '' }">
                            Semua
                        </button>

                        <!-- Sering Dibeli (Conditional) -->
                        <button x-show="isCustomerSelected" @click="categoryId = 'recommendations'"
                            class="flex-none px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all duration-200 active:scale-95"
                            :class="{ 'bg-purple-600 text-white shadow-md shadow-purple-500/30': categoryId === 'recommendations', 'bg-purple-50 text-purple-700 border border-purple-100 hover:bg-purple-100': categoryId !== 'recommendations' }"
                            style="display: none;">
                            ★ Sering Dibeli
                        </button>

                        <!-- Pinned Categories (ID 1 & 15) -->
                        <template x-for="category in categories.filter(c => c.id == 1 || c.id == 15)" :key="category.id">
                            <button @click="categoryId = category.id" class="flex-none px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all duration-200 active:scale-95"
                            :class="{ 'bg-slate-800 text-white shadow-md': categoryId === category.id, 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300': categoryId !== category.id }">
                                <span x-text="category.name"></span>
                            </button>
                        </template>
                    </div>

                    <!-- Lainnya Dropdown Toggle -->
                    <template x-if="categories.filter(c => c.id != 1 && c.id != 15).length > 0">
                        <button @click="showAll = !showAll" class="flex-none flex items-center gap-1 px-4 py-2 rounded-full text-sm font-bold bg-white border border-gray-200 hover:bg-gray-50 transition-colors">
                            <span>Lainnya</span>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showAll }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </template>
                </div>

                <!-- Lainnya Dropdown Content -->
                <div x-show="showAll" x-collapse class="mt-3 pt-3 border-t border-gray-100">
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-4 lg:grid-cols-6 gap-2">
                        <template x-for="category in categories.filter(c => c.id != 1 && c.id != 15)" :key="category.id">
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
                        class="relative bg-white rounded-xl shadow-xl hover:shadow-2xl hover:-translate-y-1 active:scale-95 transition-all duration-200 cursor-pointer border border-gray-300 overflow-hidden group"
                        :class="{
                            'ring-4 ring-blue-600 ring-offset-2': cartItemIds.includes(product.id),
                            'ring-4 ring-orange-500 ring-offset-2 shadow-orange-500/50': index === selectedIndex && !isModalOpen && !showShortcutModal && !isCartModalOpen
                        }">

                        <button @click.stop="quickAddToCart(product)" x-show="!cartItemIds.includes(product.id)" class="absolute top-1 right-1 z-10 w-8 h-8 bg-white/90 backdrop-blur-sm text-emerald-600 rounded-full hover:bg-emerald-500 hover:text-white active:scale-90 transition-all flex items-center justify-center shadow-lg border border-emerald-100 group-hover:scale-110">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        </button>

                        <div class="p-1.5 flex flex-col h-full">
                            <div class="mb-0.5 flex-grow pr-6">
                                <h3 class="text-sm font-bold text-gray-800 leading-none line-clamp-2" x-text="product.name"></h3>
                                <div class="text-[10px] text-gray-500 mt-0.5" x-text="product.code"></div>
                            </div>
                            <div class="mt-auto pt-0.5 border-t border-gray-100">
                                <div class="text-base font-extrabold text-slate-800 leading-tight" x-text="formatCurrency(product.retail_price)"></div>
                                <div class="inline-flex items-center px-1.5 py-0.5 text-[9px] font-bold rounded-full mt-0.5"
                                    :class="{
                                        'bg-emerald-100 text-emerald-700': product.stock > 5,
                                        'bg-amber-100 text-amber-700': product.stock > 0 && product.stock <= 5,
                                        'bg-rose-100 text-rose-700': product.stock <= 0
                                    }">
                                    <span x-text="product.stock > 0 ? 'Stok: ' + product.stock : 'Habis'"></span>
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
                <div x-show="cartTotal > 0" class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 bg-white text-blue-600 text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm border border-blue-100 whitespace-nowrap" x-text="formatCurrency(cartTotal)"></div>
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
                <div class="flex items-center gap-2">
                    <h2 class="font-bold text-lg text-gray-800">Keranjang</h2>
                    <button @click="shareBill()" class="text-green-600 hover:text-green-800 p-1 rounded-full hover:bg-green-50" title="Copy Struk WA">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-8.683-2.031-9.672-.272-.989-.471-1.135-.644-1.135-.174 0-.371-.006-.57-.006-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/></svg>
                    </button>
                </div>
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
    <div x-show="isModalOpen" x-cloak 
        @keydown.window="
            if (isModalOpen) {
                if (['+', '=', 'ArrowUp'].includes($event.key)) {
                    $event.preventDefault();
                    increment();
                } else if (['-', '_', 'ArrowDown'].includes($event.key)) {
                    $event.preventDefault();
                    decrement();
                }
            }
        "
        class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-[60] p-4">
        <div @click.away="closeModal()" class="bg-white rounded-2xl shadow-xl p-5 md:p-6 w-full max-w-sm mx-auto">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-bold text-gray-900 pr-4" x-text="productForModal ? productForModal.name : ''"></h3>
                <button @click="closeModal()" class="-mt-2 -mr-2 p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <template x-if="customer">
                <div class="mb-4">
                    <div class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-md px-2 py-1">
                        <div class="flex items-center min-w-0">
                            <span class="text-xs text-blue-600 mr-2 flex-shrink-0">Pelanggan:</span>
                            <p class="font-semibold text-sm text-blue-800 truncate" x-text="customer.name"></p>
                        </div>
                        <div class="flex items-center">
                            <button x-show="customer.debt > 0" @click="shareDebt()" class="p-1 text-green-600 hover:text-green-800 rounded-full hover:bg-green-100 mr-1" title="Share Tagihan WA">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-8.683-2.031-9.672-.272-.989-.471-1.135-.644-1.135-.174 0-.371-.006-.57-.006-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/></svg>
                            </button>
                            <button @click="clearCustomer()" class="p-1 text-red-500 hover:text-red-700 rounded-full hover:bg-red-100 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <div class="mb-8 text-center">
                <label for="quantity" class="block text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Atur Jumlah</label>
                <div class="flex items-center justify-center gap-6">
                    <button type="button" @click="decrement()"  class="w-16 h-16 flex items-center justify-center text-3xl bg-slate-100 text-slate-600 rounded-2xl border-2 border-slate-300 hover:bg-slate-200 hover:border-slate-400 active:scale-90 transition-all duration-200 focus:outline-none shadow-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"/></svg>
                    </button>
                    <input type="number" step="1" id="quantity" name="quantity" x-ref="quantityInput" x-model="quantity" @input.debounce.300ms="validate()" @keydown.enter.prevent.stop="if(isQuantityValid) addToCartFromModal()" class="block w-32 text-center text-4xl font-black border-2 border-slate-300 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 text-slate-800 p-2 bg-white shadow-inner" placeholder="1">
                    <button type="button" @click="increment()"  class="w-16 h-16 flex items-center justify-center text-3xl bg-blue-600 text-white rounded-2xl border-2 border-blue-700 hover:bg-blue-700 active:scale-90 transition-all duration-200 focus:outline-none shadow-lg shadow-blue-500/30">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                    </button>
                </div>
                <p class="text-sm font-medium text-slate-400 mt-4" x-text="productForModal ? 'Stok tersedia: ' + productForModal.stock : ''"></p>
                <p x-show="!isQuantityValid" class="text-sm font-bold text-rose-500 mt-2 animate-bounce" x-text="errorMessage"></p>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-8">
                <button type="button" @click="closeModal()" class="w-full px-4 py-4 text-base font-bold text-slate-600 bg-slate-100 rounded-xl border-2 border-slate-300 hover:bg-slate-200 hover:border-slate-400 focus:outline-none transition-all">Batal</button>
                <button type="button" @click="addToCartFromModal()" :disabled="!isQuantityValid" class="w-full inline-flex justify-center px-4 py-4 text-base font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl border-2 border-blue-700 shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:scale-[1.02] focus:outline-none transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none">
                    <span>Simpan</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Shortcut Help Modal -->
    <div x-show="showShortcutModal" x-cloak 
         class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div @click.away="showShortcutModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
            <div class="p-5 border-b bg-gray-50 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    Shortcut Keyboard
                </h3>
                <button @click="showShortcutModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 max-h-[70vh] overflow-y-auto">
                <!-- Group 1: Transaksi -->
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Transaksi</h4>
                    <ul class="space-y-3">
                        <li class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Bayar / Checkout</span>
                            <kbd class="px-2 py-1 text-xs font-bold text-gray-800 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">F2</kbd>
                        </li>
                        <li class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Uang Pas (di menu bayar)</span>
                            <kbd class="px-2 py-1 text-xs font-bold text-gray-800 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">F1</kbd>
                        </li>
                        <li class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Pending / Hold</span>
                            <kbd class="px-2 py-1 text-xs font-bold text-gray-800 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">F4</kbd>
                        </li>
                        <li class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Diskon Manual</span>
                            <kbd class="px-2 py-1 text-xs font-bold text-gray-800 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">F7</kbd>
                        </li>
                    </ul>
                </div>

                <!-- Group 2: Produk & Keranjang -->
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Produk & Keranjang</h4>
                    <ul class="space-y-3">
                        <li class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Cari Produk</span>
                            <div class="flex gap-1">
                                <kbd class="px-2 py-1 text-xs font-bold text-gray-800 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">/</kbd>
                                <span class="text-xs text-gray-400 self-center">atau</span>
                                <kbd class="px-2 py-1 text-xs font-bold text-gray-800 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">Spasi 2x</kbd>
                            </div>
                        </li>
                        <li class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Navigasi Produk</span>
                            <kbd class="px-2 py-1 text-xs font-bold text-gray-800 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">Arrow Down ↓</kbd>
                        </li>
                        <li class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Hapus Pencarian</span>
                            <kbd class="px-2 py-1 text-xs font-bold text-gray-800 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">Del</kbd>
                        </li>
                        <li class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Mode Scanner</span>
                            <kbd class="px-2 py-1 text-xs font-bold text-gray-800 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">F3</kbd>
                        </li>
                        <li class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Ubah Jumlah (Manual)</span>
                            <kbd class="px-2 py-1 text-xs font-bold text-gray-800 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">F8</kbd>
                        </li>
                        <li class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Cepat Tambah/Kurang Qty</span>
                            <div class="flex gap-1">
                                <kbd class="px-2 py-1 text-xs font-bold text-gray-800 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">+</kbd>
                                <kbd class="px-2 py-1 text-xs font-bold text-gray-800 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">-</kbd>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Group 3: Lainnya -->
                <div class="md:col-span-2 mt-2 pt-4 border-t border-gray-100">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Lainnya</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                        <li class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Pilih Pelanggan</span>
                            <kbd class="px-2 py-1 text-xs font-bold text-gray-800 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">F9</kbd>
                        </li>
                        <li class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Tutup Modal / Batal</span>
                            <kbd class="px-2 py-1 text-xs font-bold text-gray-800 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">Esc</kbd>
                        </li>
                    </div>
                </div>
            </div>
            <div class="p-4 bg-gray-50 border-t text-center">
                <button @click="showShortcutModal = false" class="px-6 py-2 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-colors shadow-lg shadow-blue-500/30">
                    Mengerti
                </button>
            </div>
        </div>
    </div>
</div>
</div>


<script>
    function posManager() {
        return {
            // UI State
            // Updated breakpoint to 1024px (lg) to match CSS 'hidden lg:flex'
            // This ensures tablets (768-1023px) use the mobile view (FAB) instead of empty desktop view
            isDesktop: window.innerWidth >= 1024, 
            isCartVisible: window.innerWidth >= 1024,
            isLandscape: window.matchMedia("(orientation: landscape)").matches,
            isScannerMode: false,
            isScanning: false,
            isScanning: false,
            scanningTimeout: null,
            scanQueue: [], // Queue for barcode scans
            isProcessingScan: false, // Flag to track processing status

            // Product Search State
            products: [],
            categories: [],
            searchQuery: '',
            ghostQuery: '', // Buffer for F3 Ghost Input
            categoryId: '',
            isLoading: true,
            selectedIndex: -1, // -1 means no selection
            selectedIndex: -1, // -1 means no selection
            ignoreNextSearchQueryWatch: false,
            recommendedProducts: [],
            isCustomerSelected: false,
            currentCustomerId: null,
            customer: null,

            // Cart State
            cartItemIds: [],
            cartTotal: 0,

            // Quantity Modal State
            isModalOpen: false,
            isCartModalOpen: false,
            productForModal: null,
            quantity: 1,
            isQuantityValid: true,
            isQuantityValid: true,
            isQuantityValid: true,
            errorMessage: '',

            // Shortcut Modal
            showShortcutModal: false,
            isSearchFocusedByShortcut: false,

            // Keyboard State
            barcodeBuffer: '',
            lastKeyTime: 0,
            scanTimeout: null,
            lastSpaceTime: 0,
            lastScannedBarcode: '',
            lastScanTime: 0,

            // Init
            init() {
                this.fetchCategories();
                this.fetchProducts();
                // this.initKeyboardListeners(); // Removed manual listener
                
                // Preload Sounds
                this.sounds = {
                    success: new Audio('/sounds/success.mp3'),
                    error: new Audio('/sounds/error.mp3')
                };

                // Set initial state of main navigation
                this.$store.ui.isBottomNavVisible = false;

                const handleResize = () => {
                    const wasDesktop = this.isDesktop;
                    this.isDesktop = window.innerWidth >= 1024;
                    this.isLandscape = window.matchMedia("(orientation: landscape)").matches;
                    
                    // Only change visibility if mode changes
                    if (this.isDesktop && !wasDesktop) {
                        this.isCartVisible = true;
                    } else if (!this.isDesktop && wasDesktop) {
                        this.isCartVisible = false;
                    }
                    // If staying in mobile mode (e.g. keyboard open/close), do not change isCartVisible
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
                    if (this.isScanning) return;
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

                // F3 Scanner Mode Watcher - Switch Focus to Ghost Input
                this.$watch('isScannerMode', (value) => {
                    if (value) {
                        this.$nextTick(() => {
                            if (this.$refs.scannerTrap) this.$refs.scannerTrap.focus();
                        });
                    } else {
                        // Return to search
                        this.$nextTick(() => {
                            if (this.$refs.searchInput) this.$refs.searchInput.focus();
                        });
                    }
                });
            },

            handleTrapKeydown(e) {
                // Allow control keys (F3 to toggle, Escape to exit) to bubble up to global listener
                if (e.key === 'F3' || e.key === 'Escape') {
                    return;
                }

                // Stop global listener from also trying to process this (text input)
                e.stopPropagation();
                
                if (e.key === 'Enter') {
                    e.preventDefault();
                    
                    // Robust value retrieval: Try x-model then DOM value
                    // This handles cases where x-model update lags behind high-speed scanners
                    let val = this.ghostQuery;
                    if (!val || val.length === 0) {
                        val = e.target.value;
                    }
                    
                    if (val && val.length > 2) {
                        this.handleScannedCode(val);
                        this.ghostQuery = '';
                        e.target.value = ''; // Manual clear to be safe
                    }
                }
            },

            playSound(type) {
                if (this.sounds && this.sounds[type]) {
                    // Clone node to allow overlapping sounds (fast scanning)
                    const sound = this.sounds[type].cloneNode();
                    sound.play().catch(e => console.error("Error playing sound:", e));
                }
            },

            setCustomer(customer) {
                this.isCustomerSelected = true;
                this.currentCustomerId = customer.id;
                this.customer = customer;
                this.selectedIndex = -1; // Reset product selection to avoid confusion
                this.fetchRecommendations(customer.id);
            },

            clearCustomer() {
                this.isCustomerSelected = false;
                this.currentCustomerId = null;
                this.customer = null;
                this.recommendedProducts = [];
                if (this.categoryId === 'recommendations') {
                    this.categoryId = '';
                }
            },

            fetchRecommendations(customerId) {
                fetch(`/api/products/recommendations/${customerId}`, {
                    headers: {
                        'X-XSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    credentials: 'include'
                })
                    .then(response => response.json())
                    .then(data => {
                        this.recommendedProducts = data;
                        // Optional: Auto-switch to recommendations if found
                        if (data.length > 0) this.categoryId = 'recommendations';
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

            prepareForModal() {
                this.selectedIndex = -1;
                this.$refs.searchInput.blur();
            },

            getGridColumns() {
                const width = window.innerWidth;
                if (width >= 1280) return 6; // xl
                if (width >= 640) return 4;  // sm, md, lg
                return 3;                    // mobile
            },

            // Keyboard Listeners (Refactored to Alpine Method)
            handleGlobalKeydown(e) {
                // Shortcut global
                if (e.key === 'F2') { e.preventDefault(); this.prepareForModal(); window.dispatchEvent(new CustomEvent('shortcut:pay')); return; }
                if (e.key === 'F3') { e.preventDefault(); this.isScannerMode = !this.isScannerMode; return; }
                if (e.key === 'F4') { e.preventDefault(); this.prepareForModal(); window.dispatchEvent(new CustomEvent('shortcut:hold')); return; }
                if (e.key === 'F7') { e.preventDefault(); this.prepareForModal(); window.dispatchEvent(new CustomEvent('shortcut:reduction')); return; }
                if (e.key === 'F8') { e.preventDefault(); this.prepareForModal(); window.dispatchEvent(new CustomEvent('shortcut:quantity')); return; }
                if (e.key === 'F9') { e.preventDefault(); this.prepareForModal(); window.dispatchEvent(new CustomEvent('shortcut:customer')); return; }
                if (e.key === 'Escape') { this.handleEscape(); return; }
                
                const targetIsInput = e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable;
                const targetIsSearch = e.target === this.$refs.searchInput;

                // New Shortcuts
                if ((e.key === '+' || e.key === '=') && !targetIsInput) { e.preventDefault(); window.dispatchEvent(new CustomEvent('shortcut:quantity-inc')); return; }
                if ((e.key === '-' || e.key === '_') && !targetIsInput) { e.preventDefault(); window.dispatchEvent(new CustomEvent('shortcut:quantity-dec')); return; }
                if (e.key === '/' && !targetIsInput) { e.preventDefault(); this.resetSearchAndFocus(); return; }
                
                // Delete: Clear search if not input OR if input is search box
                if (e.key === 'Delete' && (!targetIsInput || targetIsSearch)) { 
                    e.preventDefault(); 
                    this.resetSearchAndFocus(); 
                    return; 
                }
                
                if (e.key === '?' && !targetIsInput) { e.preventDefault(); this.showShortcutModal = !this.showShortcutModal; return; }

                // Ignore if modal/cart open (except Escape)
                if (this.isModalOpen || this.showShortcutModal || this.isCartModalOpen || (!this.isDesktop && this.isCartVisible)) return;

                // Double space shortcut
                if (e.key === ' ' && !targetIsInput) {
                    e.preventDefault();
                    const now = Date.now();
                    if (now - this.lastSpaceTime < 300) { 
                        this.resetSearchAndFocus(); 
                        this.lastSpaceTime = 0; 
                    } else { 
                        this.lastSpaceTime = now; 
                    }
                    return;
                }

                // Global ArrowDown: Focus search and ensure selection
                if (e.key === 'ArrowDown' && !targetIsSearch && this.barcodeBuffer.length === 0) {
                    e.preventDefault();
                    this.$refs.searchInput.focus();
                    
                    // Visual feedback
                    this.isSearchFocusedByShortcut = true;
                    setTimeout(() => this.isSearchFocusedByShortcut = false, 300);

                    // If no product selected, select the first one
                    if (this.selectedIndex === -1) {
                        if (this.products.length > 0) {
                            this.selectedIndex = 0;
                            this.scrollIntoView();
                        }
                        return;
                    }
                    // If product IS selected, we fall through to the 'Navigation' block below
                    // to handle the actual movement (Next Item)
                }

                // Navigation
                if (this.selectedIndex > -1) {
                    if(['ArrowDown','ArrowRight','ArrowUp','ArrowLeft','Enter'].includes(e.key)) {
                        // Only handle navigation if buffer is empty (not scanning)
                        if (this.barcodeBuffer.length === 0) {
                            e.preventDefault();
                            const cols = this.getGridColumns();
                            
                            if(e.key === 'ArrowRight') { 
                                this.selectedIndex = Math.min(this.products.length - 1, this.selectedIndex + 1); 
                                this.scrollIntoView(); 
                            }
                            if(e.key === 'ArrowLeft') { 
                                this.selectedIndex = Math.max(0, this.selectedIndex - 1); 
                                this.scrollIntoView(); 
                            }
                            if(e.key === 'ArrowDown') { 
                                this.selectedIndex = Math.min(this.products.length - 1, this.selectedIndex + cols); 
                                this.scrollIntoView(); 
                            }
                            if(e.key === 'ArrowUp') { 
                                this.selectedIndex = Math.max(0, this.selectedIndex - cols); 
                                this.scrollIntoView(); 
                            }
                            
                            if(e.key === 'Enter' && this.products[this.selectedIndex]) { this.openModal(this.products[this.selectedIndex]); }
                            return;
                        }
                    }
                }

                // Enter navigation from search
                if (targetIsSearch && e.key === 'ArrowDown' && this.barcodeBuffer.length === 0) {
                    e.preventDefault();
                    if (this.products.length > 0) { this.selectedIndex = 0; this.scrollIntoView(); }
                    return;
                }

                // --- OPTIMIZED BARCODE SCANNER LOGIC ---
                const now = Date.now();
                const timeDiff = now - this.lastKeyTime;
                this.lastKeyTime = now;

                // Check if input is fast (scanner usually < 30-50ms, but Bluetooth can be slower)
                const isFast = timeDiff < 150; // Increased from 60

                if (isFast) {
                    this.isScanning = true;
                    if (this.scanningTimeout) clearTimeout(this.scanningTimeout);
                    this.scanningTimeout = setTimeout(() => {
                        this.isScanning = false;
                    }, 500); // Increased from 200
                }

                // If typing in an input but it's SLOW, ignore (let it be manual input)
                // If it's FAST, we assume it's a scanner, even if focused on an input
                if (!isFast && targetIsInput && !targetIsSearch) {
                    this.barcodeBuffer = ''; // Reset buffer on manual typing
                    return; 
                }

                // Reset buffer if gap is too long
                if (timeDiff > 500) { // Increased from 100
                    this.barcodeBuffer = '';
                }

                if (e.key === 'Enter') {
                    // Determine what to process: buffer or search input
                    let codeToProcess = '';
                    
                    if (this.barcodeBuffer.length > 2) {
                        codeToProcess = this.barcodeBuffer;
                        e.preventDefault(); 
                        e.stopPropagation();
                    } else if (targetIsSearch && this.searchQuery.length > 2) {
                        // Fallback for manual entry in search box
                        codeToProcess = this.searchQuery;
                    }

                    if (codeToProcess) {
                        this.handleScannedCode(codeToProcess);
                        this.barcodeBuffer = ''; 
                        if (this.scanTimeout) clearTimeout(this.scanTimeout);
                        
                        if (targetIsSearch) {
                            this.searchQuery = ''; 
                            this.ignoreNextSearchQueryWatch = true;
                            // Optimize Auto Clear: Force render update immediately
                            if (this.$refs.searchInput) this.$refs.searchInput.value = '';
                        }
                    }
                    return;
                }

                // Ignore special keys
                if (e.key.length > 1) return;

                this.barcodeBuffer += e.key;
                
                // Auto-submit on pause (if no Enter is sent)
                if (this.scanTimeout) clearTimeout(this.scanTimeout);
                this.scanTimeout = setTimeout(() => {
                    if (this.barcodeBuffer.length > 5) {
                        this.handleScannedCode(this.barcodeBuffer);
                        this.barcodeBuffer = '';
                    }
                }, 200);
            },

            handleScannedCode(code) {
                // Push code to queue and trigger processing
                this.scanQueue.push(code);
                this.processScanQueue();

                // SELF-HEALING FOCUS:
                // Regardless of where the scan came from (global buffer or input),
                // we need to return focus to the correct element.
                this.$nextTick(() => {
                    if (this.isScannerMode) {
                        if (this.$refs.scannerTrap) this.$refs.scannerTrap.focus();
                    } else if (this.$refs.searchInput) {
                        this.$refs.searchInput.focus();
                    }
                });
            },

            processScanQueue() {
                // Process all items currently in the queue immediately
                while (this.scanQueue.length > 0) {
                    const code = this.scanQueue.shift();
                    // Fire and forget - allow parallel requests for speed
                    this.fetchProductsByBarcode(code);
                }
            },

            async fetchProductsByBarcode(barcode) {
                try {
                    // Optimistic UI: Play sound immediately? No, wait for result to know if success/error.
                    // But we want it "fast".
                    
                    const response = await fetch(`/api/products/by-code/${barcode}`, {
                        headers: {
                            'X-XSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        credentials: 'include'
                    });
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
                fetch('/api/categories', {
                    headers: {
                        'X-XSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    credentials: 'include'
                })
                    .then(response => response.json())
                    .then(data => { this.categories = data; });
            },
            fetchProducts() {
                return new Promise((resolve, reject) => {
                    this.isLoading = true;
                    const params = new URLSearchParams({ q: this.searchQuery, category_id: this.categoryId });
                    fetch(`/api/products?${params}`, {
                        headers: {
                            'X-XSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        credentials: 'include'
                    })
                        .then(response => response.json())
                        .then(data => {
                            // Fix race condition: If category is now 'recommendations', ignore this result
                            if (this.categoryId === 'recommendations') {
                                this.isLoading = false;
                                resolve();
                                return;
                            }
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

            scrollIntoView() {
                this.$nextTick(() => {
                    const element = document.getElementById('product-' + this.selectedIndex);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });
            },
            playSound(type) {
                if (this.sounds && this.sounds[type]) {
                    const sound = this.sounds[type].cloneNode();
                    sound.play().catch(e => console.error("Error playing sound:", e));
                }
            },
            shareDebt() {
                if (!this.customer || !this.customer.phone) {
                    window.Livewire.dispatch('show-alert', { type: 'error', message: 'Nomor HP pelanggan tidak tersedia.' });
                    return;
                }
                // Remove leading 0 and replace with 62 if necessary, or just use as is if already formatted
                let phone = this.customer.phone.replace(/\D/g, '');
                if (phone.startsWith('0')) {
                    phone = '62' + phone.substring(1);
                }
                
                const message = `Halo ${this.customer.name}, Anda memiliki total hutang sebesar ${this.formatCurrency(this.customer.debt)} di NanangStore. Mohon segera dilunasi. Terima kasih.`;
                const url = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
                window.open(url, '_blank');
            },
            shareBill() {
                // Dispatch event to Livewire to handle bill sharing (since we need cart items)
                window.Livewire.dispatch('share-bill');
            }
        }
    }
</script>

