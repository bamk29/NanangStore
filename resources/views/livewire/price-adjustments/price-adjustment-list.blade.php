<div class="min-h-screen bg-slate-50 pb-20">
    <!-- Header -->
    <div class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between py-4 gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard') }}" class="p-2 rounded-full hover:bg-slate-100 transition-colors">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">Penyesuaian Harga</h1>
                        <p class="text-sm text-slate-500">Update harga modal, retail, dan grosir produk dengan cepat</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        
        <!-- Form Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Form Penyesuaian Harga
                </h2>
            </div>
            
            <div class="p-6" x-data="{
                scanBuffer: '',
                scanTimeout: null,
                isScanning: false,
                search: '',
                products: [],
                isSearching: false,
                searchDebounce: null,
                boxPrice: @entangle('new_box_cost'),
                largeUnit: @entangle('new_units_in_box'),
                
                init() { 
                    $nextTick(() => { $refs.searchInput?.focus(); }); 
                },
                
                async searchProducts() {
                    if (this.search.length < 2) {
                        this.products = [];
                        return;
                    }
                    
                    this.isSearching = true;
                    try {
                        const response = await fetch(`/api/products?q=${encodeURIComponent(this.search)}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (response.ok) {
                            this.products = await response.json();
                            
                            // Auto-select if only one result
                            if (this.products.length === 1) {
                                this.selectProduct(this.products[0]);
                            }
                        }
                    } catch (error) {
                        console.error('Search error:', error);
                    } finally {
                        this.isSearching = false;
                    }
                },
                
                selectProduct(product) {
                    @this.call('selectProduct', product.id, product.name, product.cost_price, product.retail_price, product.wholesale_price, product.wholesale_min_qty);
                    this.search = product.name;
                    this.products = [];
                },
                
                handleInput() {
                    clearTimeout(this.searchDebounce);
                    this.searchDebounce = setTimeout(() => {
                        this.searchProducts();
                    }, 300);
                },
                
                handleKeyPress(event) {
                    if (event.target.tagName === 'TEXTAREA' || event.target.type === 'number') return;
                    
                    clearTimeout(this.scanTimeout);
                    this.isScanning = true;
                    
                    if (event.key === 'Enter' && this.scanBuffer.length > 3) {
                        event.preventDefault();
                        this.search = this.scanBuffer;
                        this.searchProducts();
                        this.scanBuffer = '';
                        this.isScanning = false;
                    } else if (event.key.length === 1 && !event.ctrlKey && !event.altKey) {
                        this.scanBuffer += event.key;
                        this.scanTimeout = setTimeout(() => { 
                            this.scanBuffer = ''; 
                            this.isScanning = false; 
                        }, 100);
                    } else if (event.key === 'Escape') {
                        this.search = '';
                        this.products = [];
                        this.scanBuffer = '';
                        this.isScanning = false;
                    }
                },
                
                calculateMargin(cost, retail) {
                    cost = parseFloat(cost) || 0;
                    retail = parseFloat(retail) || 0;
                    if (retail === 0) return '0%';
                    const margin = ((retail - cost) / retail) * 100;
                    return margin.toFixed(1) + '%';
                },
                
                calculateProfit(cost, price) {
                    cost = parseFloat(cost) || 0;
                    price = parseFloat(price) || 0;
                    return this.formatCurrency(price - cost);
                },
                
                calculateRecommendedCost() {
                    const box = parseFloat(this.boxPrice) || 0;
                    const unit = parseFloat(this.largeUnit) || 0;
                    if (box === 0 || unit === 0) return 0;
                    return Math.ceil(box / unit);
                },
                
                formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', { 
                        style: 'currency', 
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(amount || 0);
                }
            }" @keydown.window="handleKeyPress">
                @if (session()->has('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        {{ session('success') }}
                    </div>
                @endif
                <div x-show="isScanning" x-transition class="mb-4 p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 flex items-center gap-2">
                    <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    Scanner aktif... Scan barcode produk
                </div>

                <form wire:submit.prevent="save" class="space-y-6">
                    <!-- Product Search -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Cari Produk (Scan/Ketik)</label>
                        <div class="relative">
                            <input type="text" 
                                x-ref="searchInput"
                                x-model="search"
                                @input="handleInput"
                                class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all pr-20 text-lg"
                                placeholder="Scan barcode atau ketik nama produk..."
                                autocomplete="off">
                            
                            <!-- Right side icons -->
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 gap-2">
                                <!-- Camera Scanner Button -->
                                <button 
                                    type="button"
                                    @click="$dispatch('open-camera-scanner')"
                                    class="text-slate-400 hover:text-blue-600 transition-colors"
                                    title="Scan Kamera">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </button>

                                <!-- Clear button -->
                                <button 
                                    type="button"
                                    x-show="search.length > 0"
                                    @click="search = ''; products = []; $refs.searchInput.focus()"
                                    class="text-slate-400 hover:text-slate-600 transition-colors">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                
                                <!-- Loading spinner -->
                                <div x-show="isSearching">
                                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Camera Scanner Component -->
                        <x-camera-scanner @scan-completed="
                            search = $event.detail; 
                            $nextTick(() => { 
                                handleInput(); // Trigger search
                            });
                        " />

                        <!-- Search Results Dropdown -->
                        <div x-show="products.length > 0" x-cloak class="absolute z-50 mt-1 w-full bg-white rounded-xl shadow-xl border border-slate-200 max-h-60 overflow-y-auto">
                            <template x-for="product in products" :key="product.id">
                                <button type="button" 
                                    @click="selectProduct(product)"
                                    class="w-full text-left px-4 py-3 hover:bg-slate-50 transition-colors border-b border-slate-100 last:border-0 group">
                                    <div class="font-medium text-slate-800 group-hover:text-blue-600" x-text="product.name"></div>
                                    <div class="text-xs text-slate-500">
                                        Modal: <span class="font-bold" x-text="'Rp ' + (product.cost_price || 0).toLocaleString('id-ID')"></span>
                                        | Retail: <span class="font-bold" x-text="'Rp ' + (product.retail_price || 0).toLocaleString('id-ID')"></span>
                                    </div>
                                </button>
                            </template>
                        </div>
                        @error('product_id') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Selected Product Display -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Produk Terpilih</label>
                        <div class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-600">
                            {{ $product_name ?? 'Belum ada produk dipilih' }}
                        </div>
                    </div>

                    @if($product_id)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Current Prices -->
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                            <h3 class="text-sm font-semibold text-slate-700 mb-3">Harga Saat Ini</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Harga Modal:</span>
                                    <span class="font-bold text-slate-800">Rp {{ number_format($current_cost, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Harga Retail:</span>
                                    <span class="font-bold text-slate-800">Rp {{ number_format($current_retail, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Harga Grosir:</span>
                                    <span class="font-bold text-slate-800">Rp {{ number_format($current_wholesale, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Min Qty Grosir:</span>
                                    <span class="font-bold text-slate-800">{{ $current_min_qty }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- New Prices -->
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                            <h3 class="text-sm font-semibold text-blue-700 mb-3">Harga Baru</h3>
                            <div class="space-y-3">
                                <!-- Box Price Calculator -->
                                <div class="p-3 bg-white border border-blue-300 rounded-lg">
                                    <div class="text-xs font-semibold text-blue-700 mb-2">💡 Kalkulator Harga Modal</div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] text-slate-600 mb-1">Harga Box</label>
                                            <input type="number" 
                                                x-model.number="boxPrice" 
                                                class="w-full rounded border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-xs py-1"
                                                placeholder="0" min="0" step="1000">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] text-slate-600 mb-1">Satuan Besar</label>
                                            <input type="number" 
                                                x-model.number="largeUnit" 
                                                class="w-full rounded border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-xs py-1"
                                                placeholder="0" min="0">
                                        </div>
                                    </div>
                                    <div class="mt-2 p-2 bg-emerald-50 border border-emerald-200 rounded flex items-center justify-between">
                                        <div>
                                            <div class="text-[10px] text-emerald-600 font-medium">Rekomendasi Harga Modal</div>
                                            <div class="text-sm font-bold text-emerald-700" x-text="formatCurrency(calculateRecommendedCost())"></div>
                                        </div>
                                        <button type="button" @click="$wire.set('new_cost', calculateRecommendedCost())" class="px-3 py-1.5 text-xs font-bold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                                            Atur
                                        </button>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-xs text-slate-600 mb-1">Harga Modal</label>
                                    <input type="number" wire:model.live="new_cost" 
                                        class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-sm"
                                        placeholder="0" min="0" step="100">
                                    @error('new_cost') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-600 mb-1">
                                        Harga Retail 
                                        <span x-text="'(' + calculateMargin({{ $new_cost }}, {{ $new_retail }}) + ')'" class="text-blue-600 font-semibold ml-1"></span>
                                        <span x-text="'(Untung ' + calculateProfit({{ $new_cost }}, {{ $new_retail }}) + ')'" class="text-emerald-600 font-semibold ml-1"></span>
                                    </label>
                                    <input type="number" wire:model.live="new_retail" 
                                        class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-sm"
                                        placeholder="0" min="0" step="100">
                                    @error('new_retail') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-600 mb-1">
                                        Harga Grosir 
                                        <span x-text="'(' + calculateMargin({{ $new_cost }}, {{ $new_wholesale }}) + ')'" class="text-blue-600 font-semibold ml-1"></span>
                                        <span x-text="'(Untung ' + calculateProfit({{ $new_cost }}, {{ $new_wholesale }}) + ')'" class="text-emerald-600 font-semibold ml-1"></span>
                                    </label>
                                    <input type="number" wire:model.live="new_wholesale" 
                                        class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-sm"
                                        placeholder="0" min="0" step="100">
                                    @error('new_wholesale') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-600 mb-1">Min Qty Grosir</label>
                                    <input type="number" wire:model.live="new_min_qty" 
                                        class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-sm"
                                        placeholder="0" min="0">
                                    @error('new_min_qty') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
                        <textarea wire:model="notes" rows="3" 
                            class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all"
                            placeholder="Alasan penyesuaian harga..."></textarea>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" wire:click="resetForm" class="px-5 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 font-medium transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- History Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Riwayat Perubahan Harga
                </h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold">
                        <tr>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Produk</th>
                            <th class="px-6 py-4 text-center">Harga Modal</th>
                            <th class="px-6 py-4 text-center">Harga Retail</th>
                            <th class="px-6 py-4 text-center">Harga Grosir</th>
                            <th class="px-6 py-4">User</th>
                            <th class="px-6 py-4">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($adjustments as $adjustment)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $adjustment->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4 font-medium text-slate-800">{{ $adjustment->product->name }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-xs space-y-1">
                                        <div class="text-slate-400 line-through">Rp {{ number_format($adjustment->old_cost_price, 0, ',', '.') }}</div>
                                        <div class="font-bold text-green-600">Rp {{ number_format($adjustment->new_cost_price, 0, ',', '.') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-xs space-y-1">
                                        <div class="text-slate-400 line-through">Rp {{ number_format($adjustment->old_retail_price, 0, ',', '.') }}</div>
                                        <div class="font-bold text-green-600">Rp {{ number_format($adjustment->new_retail_price, 0, ',', '.') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-xs space-y-1">
                                        <div class="text-slate-400 line-through">Rp {{ number_format($adjustment->old_wholesale_price, 0, ',', '.') }}</div>
                                        <div class="font-bold text-green-600">Rp {{ number_format($adjustment->new_wholesale_price, 0, ',', '.') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ $adjustment->user->name }}</td>
                                <td class="px-6 py-4 text-slate-500 italic">{{ Str::limit($adjustment->notes, 30) ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <p>Belum ada riwayat perubahan harga</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($adjustments->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $adjustments->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
