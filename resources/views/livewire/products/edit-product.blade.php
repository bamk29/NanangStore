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
                    <input type="text" id="code" wire:model="code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100" readonly>
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
                </div>
            </div>
            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi (Opsional)</label>
                <textarea id="description" wire:model="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
            </div>
        </div>

        <!-- Pengaturan Satuan -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-6">Pengaturan Satuan & Harga Jual</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label for="base_unit_id" class="block text-sm font-medium text-gray-700">Satuan Dasar (Eceran)</label>
                    <select id="base_unit_id" wire:model="base_unit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">Pilih Satuan</option>@foreach($base_units as $item)<option value="{{ $item->id }}">{{ $item->name }} ({{ $item->code }})</option>@endforeach</select>
                </div>
                <div>
                    <label for="box_unit_id" class="block text-sm font-medium text-gray-700">Satuan Besar (Grosir)</label>
                    <select id="box_unit_id" wire:model="box_unit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">Pilih Satuan</option>@foreach($box_units as $item)<option value="{{ $item->id }}">{{ $item->name }} ({{ $item->code }})</option>@endforeach</select>
                </div>
                <div>
                    <label for="units_in_box" class="block text-sm font-medium text-gray-700">Isi Satuan Besar</label>
                    <input type="number" id="units_in_box" wire:model.live="units_in_box" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Contoh: 12">
                </div>
                <div>
                    <label for="unit_price" class="block text-sm font-medium text-gray-700">Harga Jual Eceran</label>
                    <input type="number" id="unit_price" wire:model="retail_price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="box_price" class="block text-sm font-medium text-gray-700">Harga Jual Grosir (per box)</label>
                    <input type="number" id="box_price" wire:model="wholesale_price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="wholesale_min_qty" class="block text-sm font-medium text-gray-700">Min. Kuantitas Grosir</label>
                    <input type="number" id="wholesale_min_qty" wire:model="wholesale_min_qty" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>
        </div>

        <!-- Stok & Modal -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-6">Stok & Modal</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700">Total Stok (Satuan Dasar)</label>
                    <input type="number" id="stock" wire:model="stock" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="box_stock" class="block text-sm font-medium text-gray-700">Stok Boks</label>
                    <input type="number" id="box_stock" wire:model="box_stock" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <p class="mt-1 text-xs text-gray-500">Sistem menghitung: <span class="font-semibold">{{ $calculatedBoxStock }}</span> boks.</p>
                </div>
                <div>
                    <label for="box_cost" class="block text-sm font-medium text-gray-700">Modal per Box dari Supplier</label>
                    <input type="number" id="box_cost" wire:model.live="box_cost" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="unit_cost" class="block text-sm font-medium text-gray-700">Modal per Satuan Dasar</label>
                    <input type="number" id="unit_cost" wire:model="cost_price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <p class="mt-1 text-xs text-gray-500">Rekomendasi Modal: <span class="font-semibold">Rp {{ number_format($this->recommendedCostPrice, 0, ',', '.') }}</span> (dari Modal per Box / Isi Satuan Besar)</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="button" wire:click="printLabel" wire:loading.attr="disabled" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-6 py-3 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 mr-3">
                <span wire:loading wire:target="printLabel" class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full mr-2" role="status" aria-hidden="true"></span>
                Cetak Label
            </button>
            <button type="submit" class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-blue-700">Perbarui Produk</button>
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
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                    return;
                }

                if (e.key === 'Enter') {
                    if (barcode.length > 3) {
                        this.$wire.set('code', barcode);
                        this.$nextTick(() => {
                            document.getElementById('code').focus();
                        });
                    }
                    barcode = '';
                    return;
                }

                if (e.key.length > 1) return;

                const now = Date.now();
                if (now - lastKeystrokeTime > 100) {
                    barcode = '';
                }
                barcode += e.key;
                lastKeystrokeTime = now;
            });
        }
    }
}
</script>
