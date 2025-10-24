{{-- resources/views/livewire/products/product-import.blade.php --}}
<div>
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold text-gray-900">Import Produk</h1>
                <p class="mt-2 text-sm text-gray-700">Unggah file Excel (.xlsx, .xls, .csv) untuk menambahkan atau memperbarui produk secara massal.</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Kembali ke Daftar Produk
                </a>
            </div>
        </div>

        <div class="mt-8 max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
            <form wire:submit.prevent="import">
                <div class="mb-4">
                    <label for="file" class="block text-sm font-medium text-gray-700">Pilih File Excel/CSV</label>
                    <input type="file" id="file" wire:model="file" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                    @error('file') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            wire:loading.attr="disabled" wire:target="file, import">
                        <span wire:loading.remove wire:target="import">Mulai Import</span>
                        <span wire:loading wire:target="import">Mengimpor...</span>
                    </button>
                    @if ($importing)
                        <p class="text-sm text-gray-600">Proses import sedang berjalan...</p>
                    @endif
                    @if ($importFinished)
                        <p class="text-sm text-green-600">Import selesai!</p>
                    @endif
                </div>
            </form>
        </div>

        <div class="mt-6 text-center text-sm text-gray-600">
            <p>Pastikan file Excel Anda memiliki kolom dengan nama yang sesuai: `code`, `name`, `description`, `stock`, `retail_price`, `wholesale_price`, `wholesale_min_qty`, `cost_price`, `units_in_box`, `category_name`, `supplier_name`, `base_unit_name`, `box_unit_name`.</p>
            <p class="mt-2">Untuk `category_name`, `supplier_name`, `base_unit_name`, dan `box_unit_name`, gunakan nama yang sesuai dari sheet "Daftar Kategori", "Daftar Supplier", dan "Daftar Unit" di file Excel yang sama.</p>
            <p class="mt-2">Anda dapat menggunakan fitur "Data Validation List" di Excel untuk membuat dropdown pilihan dari sheet-sheet tersebut agar lebih mudah.</p>
        </div>
    </div>
</div>
