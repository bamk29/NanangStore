<div x-data="productFormScanner()" x-init="init()" class="p-4 sm:p-6 lg:p-8">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">Ubah Data Produk</h2>
        <a href="{{ route('products.index') }}" class="text-sm text-blue-600 hover:text-blue-700">&larr; Kembali ke Daftar Produk</a>
    </div>

    <form wire:submit.prevent="save" class="space-y-8">
        <!-- Informasi Dasar -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-6">Informasi Dasar</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Produk</label>
                    <input type="text" id="name" wire:model="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700">Kode Produk</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="text" id="code" wire:model="code" class="block w-full rounded-none rounded-l-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <span class="inline-flex items-center rounded-r-md border border-l-0 border-gray-300 bg-gray-50 px-3 text-gray-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </span>
                    </div>
                    @error('code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Kategori</label>
                    <select id="category_id" wire:model="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">Pilih Kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select>
                    @error('category_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier (Opsional)</label>
                    <select id="supplier_id" wire:model="supplier_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">Pilih Supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>@endforeach</select>
                    @error('supplier_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi (Opsional)</label>
                <textarea id="description" wire:model="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Pengaturan Satuan -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-6">Pengaturan Satuan & Harga Jual</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label for="base_unit_id" class="block text-sm font-medium text-gray-700">Satuan Dasar (Eceran)</label>
                    <select id="base_unit_id" wire:model="base_unit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">Pilih Satuan</option>@foreach($base_units as $item)<option value="{{ $item->id }}">{{ $item->name }} ({{ $item->code }})</option>@endforeach</select>
                    @error('base_unit_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="box_unit_id" class="block text-sm font-medium text-gray-700">Satuan Besar (Grosir)</label>
                    <select id="box_unit_id" wire:model="box_unit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">Pilih Satuan</option>@foreach($box_units as $item)<option value="{{ $item->id }}">{{ $item->name }} ({{ $item->code }})</option>@endforeach</select>
                    @error('box_unit_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="units_in_box" class="block text-sm font-medium text-gray-700">Isi Satuan Besar</label>
                    <input type="text" inputmode="decimal" id="units_in_box"
                           wire:model="units_in_box"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Contoh: 12">
                    @error('units_in_box') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="retail_price" class="block text-sm font-medium text-gray-700">Harga Jual Eceran</label>
                    <input type="text" inputmode="decimal" id="retail_price"
                           wire:model="retail_price"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('retail_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="wholesale_price" class="block text-sm font-medium text-gray-700">Harga Jual Grosir (per box)</label>
                    <input type="text" inputmode="decimal" id="wholesale_price"
                           wire:model="wholesale_price"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('wholesale_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="wholesale_min_qty" class="block text-sm font-medium text-gray-700">Min. Kuantitas Grosir</label>
                    <input type="text" inputmode="decimal" id="wholesale_min_qty"
                           wire:model="wholesale_min_qty"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('wholesale_min_qty') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Stok & Modal -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-6">Stok & Modal</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700">Total Stok (Satuan Dasar)</label>
                    <input type="text" inputmode="decimal" id="stock"
                           wire:model="stock"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('stock') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="box_stock" class="block text-sm font-medium text-gray-700">Stok Boks</label>
                    <input type="text" inputmode="decimal" id="box_stock"
                           wire:model="calculatedBoxStock"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100" readonly>
                    <p class="mt-1 text-xs text-gray-500">Dihitung otomatis dari total stok.</p>
                </div>
                <div>
                    <label for="box_cost" class="block text-sm font-medium text-gray-700">Modal per Box dari Supplier</label>
                    <input type="text" inputmode="decimal" id="box_cost"
                           wire:model="box_cost"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('box_cost') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="cost_price" class="block text-sm font-medium text-gray-700">Modal per Satuan Dasar</label>
                    <input type="text" inputmode="decimal" id="cost_price"
                           wire:model="cost_price"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('cost_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <p class="mt-1 text-xs text-gray-500">Rekomendasi Modal: <span class="font-semibold">Rp {{ number_format($this->getRecommendedCostPriceProperty(), 0, ',', '.') }}</span></p>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="button" wire:click="printLabel" wire:loading.attr="disabled" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-6 py-3 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 mr-3">
                <span wire:loading wire:target="printLabel" class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full mr-2" role="status" aria-hidden="true"></span>
                Cetak Label
            </button>
            <button type="submit" class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-blue-700">
                <span wire:loading wire:target="save" class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full mr-2" role="status" aria-hidden="true"></span>
                Perbarui Produk
            </button>
        </div>
    </form>
</div>

<script>
function productFormScanner() {
    return {
        init() {
            let barcode = '';
            let lastKeystrokeTime = 0;

            window.addEventListener('keydown', (e) => {
                if (e.target.id === 'code') {
                    return;
                }
                const isTyping = e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA';
                const now = Date.now();
                if (now - lastKeystrokeTime > 100) {
                    barcode = '';
                }
                if (e.key === 'Enter') {
                    if (barcode.length > 3) {
                        e.preventDefault();
                        this.$wire.set('code', barcode);
                        this.$nextTick(() => {
                            document.getElementById('code').focus();
                        });
                    }
                    barcode = '';
                    return;
                }
                if (e.key.length > 1) return;
                barcode += e.key;
                lastKeystrokeTime = now;
            });
        }
    }
}
</script>