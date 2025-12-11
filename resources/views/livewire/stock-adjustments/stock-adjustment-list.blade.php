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
                        <h1 class="text-2xl font-bold text-slate-800">Stok Opname</h1>
                        <p class="text-sm text-slate-500">Penyesuaian stok untuk barang rusak, hilang, atau pemakaian internal</p>
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
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    Form Penyesuaian
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
                    @this.call('selectProduct', product.id, product.name, product.stock);
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
                    if (event.target.tagName === 'TEXTAREA') return;
                    
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
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
                                                Stok Saat Ini: <span class="font-bold" x-text="product.stock"></span>
                                                <span x-show="product.barcode" class="ml-2 text-slate-400">| Barcode: <span x-text="product.barcode"></span></span>
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
                            
                            @if($type === 'set_stock' && $product_id)
                                <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-xs text-blue-600 font-medium">Stok Saat Ini</div>
                                            <div class="text-2xl font-bold text-blue-700">{{ $current_stock }}</div>
                                        </div>
                                        @if($quantity && $quantity != $current_stock)
                                            <div>
                                                <div class="text-xs text-blue-600 font-medium">Perubahan</div>
                                                <div class="text-2xl font-bold {{ ($quantity - $current_stock) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ ($quantity - $current_stock) >= 0 ? '+' : '' }}{{ $quantity - $current_stock }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Quantity -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    @if($type === 'set_stock')
                                        Stok Baru (Setelah Penyesuaian)
                                    @else
                                        Jumlah (Keluar)
                                    @endif
                                </label>
                                <input type="number" 
                                    wire:model.live="quantity" 
                                    class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all text-lg font-semibold"
                                    placeholder="0"
                                    min="0">
                                @error('quantity') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                                
                                @if($product_id)
                                    <div class="mt-3 p-3 bg-slate-50 border border-slate-200 rounded-lg">
                                        <div class="text-xs font-medium text-slate-600 mb-2">Data Stok:</div>
                                        <div class="grid grid-cols-2 gap-2 text-xs">
                                            <div>
                                                <span class="text-slate-500">Stok Saat Ini:</span>
                                                <span class="font-bold text-slate-800 ml-1">{{ $current_stock }}</span>
                                            </div>
                                            @if($type === 'set_stock' && $quantity)
                                                <div>
                                                    <span class="text-slate-500">Stok Baru:</span>
                                                    <span class="font-bold text-blue-600 ml-1">{{ $quantity }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Type -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tipe Penyesuaian</label>
                                <select wire:model.live="type" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all">
                                    @foreach($types as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('type') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
                                <textarea wire:model="notes" rows="4" 
                                    class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all"
                                    placeholder="Tulis alasan penyesuaian stok..."></textarea>
                            </div>
                            
                            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600">
                                <strong>Tips:</strong>
                                <ul class="list-disc list-inside mt-1 space-y-1">
                                    <li>Gunakan scanner barcode untuk input cepat</li>
                                    <li>Tekan <kbd class="px-1 py-0.5 bg-white border rounded">Esc</kbd> untuk clear pencarian</li>
                                    <li>Set Stok: Atur stok ke nilai tertentu</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" wire:click="resetForm" class="px-5 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 font-medium transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-lg shadow-blue-500/30 transition-all active:scale-95">
                            Simpan Penyesuaian
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- History Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Riwayat Penyesuaian
                </h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold">
                        <tr>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Produk</th>
                            <th class="px-6 py-4 text-center">Jumlah</th>
                            <th class="px-6 py-4 text-center">Stok Final</th>
                            <th class="px-6 py-4">Tipe</th>
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
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $adjustment->quantity >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $adjustment->quantity >= 0 ? '+' : '' }}{{ $adjustment->quantity }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                        {{ $adjustment->product->stock }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ $types[$adjustment->type] ?? $adjustment->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ $adjustment->user->name }}</td>
                                <td class="px-6 py-4 text-slate-500 italic">{{ Str::limit($adjustment->notes, 30) ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                        <p>Belum ada riwayat penyesuaian stok</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 border-t border-slate-100">
                {{ $adjustments->links() }}
            </div>
        </div>
    </div>
</div>