<div>
    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-bold text-gray-900">Laporan Penjualan Produk</h1>
                <p class="mt-2 text-sm text-gray-700">Analisis performa setiap produk berdasarkan penjualan, kuantitas, dan keuntungan.</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="mt-6 p-4 bg-white rounded-lg shadow">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                    <input wire:model.lazy="startDate" type="date" id="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                    <input wire:model.lazy="endDate" type="date" id="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="categoryFilter" class="block text-sm font-medium text-gray-700">Kategori</label>
                    <select wire:model.lazy="categoryFilter" id="categoryFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="all">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4 border-t pt-4 flex justify-between items-center">
                <div>
                    <span class="text-sm font-medium text-gray-700">Urutkan Berdasarkan:</span>
                    <span class="isolate inline-flex rounded-md shadow-sm ml-2">
                        <button wire:click="$set('sortBy', 'total_profit')" type="button" class="relative inline-flex items-center rounded-l-md border px-3 py-2 text-sm font-medium {{ $sortBy === 'total_profit' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                            Paling Untung
                        </button>
                        <button wire:click="$set('sortBy', 'total_sales')" type="button" class="relative -ml-px inline-flex items-center border px-3 py-2 text-sm font-medium {{ $sortBy === 'total_sales' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                            Penjualan Tertinggi
                        </button>
                        <button wire:click="$set('sortBy', 'total_quantity')" type="button" class="relative -ml-px inline-flex items-center rounded-r-md border px-3 py-2 text-sm font-medium {{ $sortBy === 'total_quantity' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                            Paling Laris
                        </button>
                    </span>
                </div>
                <button wire:click="runReport" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                    Terapkan
                </button>
            </div>
        </div>

        <!-- Data Table -->
        <div wire:loading.remove class="-mx-4 mt-8 shadow ring-1 ring-black ring-opacity-5 sm:-mx-6 md:mx-0 md:rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Peringkat</th>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Produk</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Kategori</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Jml Terjual</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Penjualan</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Keuntungan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($productsData as $index => $data)
                        <tr>
                            <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $index + 1 }}</td>
                            <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900">{{ $data['product_name'] }}</td>
                            <td class="px-3 py-4 text-sm text-gray-500">{{ $data['category_name'] }}</td>
                            <td class="px-3 py-4 text-sm text-gray-500 font-medium">{{ number_format($data['total_quantity']) }}</td>
                            <td class="px-3 py-4 text-sm text-gray-500">Rp {{ number_format($data['total_sales'], 0, ',', '.') }}</td>
                            <td class="px-3 py-4 text-sm text-green-600 font-bold">Rp {{ number_format($data['total_profit'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada data penjualan produk untuk periode dan filter yang dipilih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Loading Indicator -->
        <div wire:loading.flex class="w-full items-center justify-center py-10">
             <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-600">Memuat laporan...</span>
        </div>

    </div>
</div>
