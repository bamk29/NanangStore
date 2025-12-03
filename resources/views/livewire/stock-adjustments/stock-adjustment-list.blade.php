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
            
            <div class="p-6">
                @if (session()->has('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        {{ session('success') }}
                    </div>
                @endif

                <form wire:submit.prevent="save" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Product Search -->
                            <div class="relative">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Cari Produk</label>
                                <div class="relative">
                                    <input type="text" 
                                        wire:model.debounce.300ms="search" 
                                        class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all pl-10"
                                        placeholder="Ketik nama atau kode produk...">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </div>
                                </div>

                                <!-- Search Results Dropdown -->
                                @if(!empty($search) && count($products) > 0)
                                    <div class="absolute z-50 mt-1 w-full bg-white rounded-xl shadow-xl border border-slate-200 max-h-60 overflow-y-auto">
                                        @foreach($products as $product)
                                            <button type="button" 
                                                wire:click="selectProduct({{ $product->id }}, '{{ $product->name }}')"
                                                class="w-full text-left px-4 py-3 hover:bg-slate-50 transition-colors border-b border-slate-100 last:border-0 group">
                                                <div class="font-medium text-slate-800 group-hover:text-blue-600">{{ $product->name }}</div>
                                                <div class="text-xs text-slate-500">Stok Saat Ini: <span class="font-bold">{{ $product->stock }}</span></div>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif(!empty($search) && count($products) === 0)
                                    <div class="absolute z-50 mt-1 w-full bg-white rounded-xl shadow-xl border border-slate-200 p-4 text-center text-slate-500 text-sm">
                                        Produk tidak ditemukan
                                    </div>
                                @endif
                                @error('product_id') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Selected Product Display -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Produk Terpilih</label>
                                <div class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-600">
                                    {{ $product_name ?? 'Belum ada produk dipilih' }}
                                </div>
                            </div>

                            <!-- Quantity -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Jumlah (Keluar)</label>
                                <input type="number" 
                                    wire:model="quantity" 
                                    class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all"
                                    placeholder="0">
                                @error('quantity') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Type -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tipe Penyesuaian</label>
                                <select wire:model="type" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all">
                                    <option value="">Pilih Tipe</option>
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
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                        {{ $adjustment->quantity }}
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
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
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