<div x-data="productListScanner()" x-init="init()">
    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold text-gray-900">Manajemen Produk</h1>
                <p class="mt-2 text-sm text-gray-700">Kelola semua produk, stok, dan harga di seluruh toko Anda.</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none flex space-x-2">
                <a href="{{ route('products.import') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Import Excel
                </a>
                <button wire:click="export" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Export Excel
                </button>
                <a href="{{ route('products.create') }}" class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                    + Tambah Produk
                </a>
            </div>
        </div>

        <!-- Pesan Sukses -->
        @if (session()->has('message'))
            <div class="mt-4 rounded-md bg-green-50 p-4">
                <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
            </div>
        @endif

        <!-- Filter -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <input x-ref="searchInput" wire:model.live.debounce.300ms="search" type="text" placeholder="Cari produk (nama atau kode)..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <select wire:model.live="categoryFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Semua Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Tabel Produk -->
        <div class="-mx-4 mt-8 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:-mx-6 md:mx-0 md:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" wire:click="sortBy('name')" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 cursor-pointer">Nama Produk</th>
                        <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">Kategori</th>
                        <th scope="col" wire:click="sortBy('stock')" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell cursor-pointer">Stok</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Harga Eceran</th>
                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($products as $product)
                        <tr>
                            <td class="w-full max-w-0 py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:w-auto sm:max-w-none sm:pl-6">
                                <p>{{ $product->name }}</p>
                                <dl class="font-normal lg:hidden"><dt class="sr-only">Kategori</dt><dd class="mt-1 truncate text-gray-700">{{ $product->category->name }}</dd></dl>
                            </td>
                            <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">{{ $product->category->name }}</td>
                            <td class="hidden px-3 py-4 text-sm text-gray-500 sm:table-cell">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->stock > 10 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $product->stock }}</span>
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-500">Rp {{ number_format($product->retail_price, 0, ',', '.') }}</td>
                            <td class="py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('products.edit', $product) }}" class="p-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 hover:text-blue-800" title="Edit">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    <button wire:click="$set('productToDelete', {{ $product->id }})" class="p-2 rounded-full bg-red-100 text-red-600 hover:bg-red-200 hover:text-red-800" title="Hapus">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada produk yang ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $products->links() }}</div>
    </div>

    <!-- Modal Hapus Produk -->
    @if($productToDelete)
    <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-bold mb-2">Hapus Produk</h3>
            <p class="text-sm text-gray-600 mb-4">Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex justify-end space-x-4">
                <button wire:click="$set('productToDelete', null)" class="px-4 py-2 rounded-lg text-gray-600 bg-gray-100 hover:bg-gray-200">Batal</button>
                <button wire:click="deleteProduct" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Ya, Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
function productListScanner() {
    return {
        init() {
            let barcode = '';
            let lastKeystrokeTime = 0;

            window.addEventListener('keydown', (e) => {
                // If the user is typing in an input, let them type normally.
                // The time-based buffer reset will prevent slow typing from being treated as a scan.
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                    // unless it's our search input, in which case we let the scanner logic proceed
                    if (e.target !== this.$refs.searchInput) {
                        return;
                    }
                }

                const now = Date.now();
                // Reset buffer if keystrokes are too slow (manual typing)
                if (now - lastKeystrokeTime > 100) {
                    barcode = '';
                }

                if (e.key === 'Enter') {
                    if (barcode.length > 3) {
                        e.preventDefault(); // Prevent any form submission
                        this.$wire.set('search', barcode);
                        this.$nextTick(() => {
                            this.$refs.searchInput.focus();
                        });
                    }
                    barcode = '';
                    return;
                }

                if (e.key.length > 1) return; // Ignore keys like Shift, Ctrl, etc.

                barcode += e.key;
                lastKeystrokeTime = now;
            });
        }
    }
}
</script>