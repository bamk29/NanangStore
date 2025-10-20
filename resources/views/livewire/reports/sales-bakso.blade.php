<div>
    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-bold text-gray-900">Laporan Penjualan Bakso</h1>
                <p class="mt-2 text-sm text-gray-700">Analisis penjualan dan keuntungan khusus untuk produk dalam kategori Bakso.</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="mt-6 p-4 bg-white rounded-lg shadow">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                    <input wire:model.lazy="startDate" type="date" id="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                    <input wire:model.lazy="endDate" type="date" id="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="groupBy" class="block text-sm font-medium text-gray-700">Kelompokkan</label>
                    <select wire:model.lazy="groupBy" id="groupBy" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="date">Per Tanggal</option>
                        <option value="product">Per Produk</option>
                    </select>
                </div>
                <div>
                    <label for="paymentMethod" class="block text-sm font-medium text-gray-700">Pembayaran</label>
                    <select wire:model.lazy="paymentMethod" id="paymentMethod" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="all">Semua</option>
                        <option value="cash">Cash</option>
                        <option value="debt">Hutang</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 text-right">
                <button wire:click="runReport" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Terapkan Filter
                </button>
            </div>
        </div>

        <!-- Summary Metrics -->
        <div wire:loading.remove>
            <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01"/></svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Penjualan</dt>
                                    <dd class="text-lg font-medium text-gray-900">Rp {{ number_format($summaryMetrics['total_sales'] ?? 0, 0, ',', '.') }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H9a2 2 0 00-2 2v2m10 0a2 2 0 012 2v3a2 2 0 01-2 2H7a2 2 0 01-2-2v-3a2 2 0 012-2h10zm-4 4h.01"/></svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Keuntungan Bersih</dt>
                                    <dd class="text-lg font-medium text-green-600">Rp {{ number_format($summaryMetrics['total_profit'] ?? 0, 0, ',', '.') }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12v- китайский"/></svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Transaksi</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($summaryMetrics['total_transactions'] ?? 0) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Item Terjual</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($summaryMetrics['total_items_sold'] ?? 0) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Data Table -->
            <div class="-mx-4 mt-8 overflow-x-auto shadow ring-1 ring-black ring-opacity-5 sm:-mx-6 md:mx-0 md:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">{{ $groupBy === 'date' ? 'Tanggal' : 'Produk' }}</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Penjualan</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Keuntungan</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Item Terjual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($detailedData as $data)
                            <tr>
                                <td class="w-full max-w-0 py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:w-auto sm:max-w-none sm:pl-6">{{ $data['key'] }}</td>
                                <td class="px-3 py-4 text-sm text-gray-500">Rp {{ number_format($data['sales'], 0, ',', '.') }}</td>
                                <td class="px-3 py-4 text-sm text-green-600 font-medium">Rp {{ number_format($data['profit'], 0, ',', '.') }}</td>
                                <td class="px-3 py-4 text-sm text-gray-500">{{ number_format($data['items_sold']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada data untuk periode dan filter yang dipilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
