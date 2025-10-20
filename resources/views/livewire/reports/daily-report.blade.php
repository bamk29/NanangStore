<div>
    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Header and Filters -->
        <div class="sm:flex sm:items-center print:hidden">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-bold text-gray-900">Laporan Harian</h1>
                <p class="mt-2 text-sm text-gray-700">Ringkasan penjualan dan transaksi untuk satu hari.</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <button onclick="window.print()" type="button" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                    Print Laporan
                </button>
            </div>
        </div>

        <div class="mt-6 p-4 bg-white rounded-lg shadow print:hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="selectedDate" class="block text-sm font-medium text-gray-700">Pilih Tanggal</label>
                    <input wire:model="selectedDate" wire:change="runReport" type="date" id="selectedDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Filter Cepat per Toko</label>
                    <div class="mt-1 isolate inline-flex rounded-md shadow-sm w-full">
                        <button wire:click="setStoreFilter('all')" type="button" class="relative inline-flex items-center justify-center rounded-l-md border px-3 py-2 text-sm font-medium w-1/3 {{ $storeFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                            Semua
                        </button>
                        <button wire:click="setStoreFilter('bakso')" type="button" class="relative -ml-px inline-flex items-center justify-center border px-3 py-2 text-sm font-medium w-1/3 {{ $storeFilter === 'bakso' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                            Bakso
                        </button>
                        <button wire:click="setStoreFilter('nanang_store')" type="button" class="relative -ml-px inline-flex items-center justify-center rounded-r-md border px-3 py-2 text-sm font-medium w-1/3 {{ $storeFilter === 'nanang_store' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                            Toko
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Content -->
        <div id="report-content" class="mt-8">
            <!-- Print Header -->
            <div class="hidden print:block mb-8">
                <h1 class="text-2xl font-bold">Laporan Harian - {{ \Carbon\Carbon::parse($selectedDate)->format('d F Y') }}</h1>
                <p>Dicetak pada: {{ now()->format('d M Y, H:i') }}</p>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div class="bg-white overflow-hidden shadow rounded-lg print:shadow-none print:border">
                    <div class="p-5">
                        <h3 class="text-sm font-medium text-gray-500 truncate">Total Penjualan</h3>
                        <p class="mt-1 text-2xl font-semibold text-gray-900">Rp {{ number_format($summary['total_sales'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow rounded-lg print:shadow-none print:border">
                    <div class="p-5">
                        <h3 class="text-sm font-medium text-gray-500 truncate">Total Keuntungan</h3>
                        <p class="mt-1 text-2xl font-semibold text-green-600">Rp {{ number_format($summary['total_profit'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow rounded-lg print:shadow-none print:border">
                    <div class="p-5">
                        <h3 class="text-sm font-medium text-gray-500 truncate">Jumlah Transaksi</h3>
                        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $summary['total_transactions'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Payment Breakdown -->
            <div class="mt-8 bg-white shadow rounded-lg p-6 print:shadow-none print:border">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Rincian Metode Pembayaran</h3>
                <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div class="px-4 py-5 bg-gray-50 rounded-lg overflow-hidden sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">Penjualan Tunai</dt>
                        <dd class="mt-1 text-xl font-semibold text-gray-900">Rp {{ number_format($summary['sales_by_payment']['cash'] ?? 0, 0, ',', '.') }}</dd>
                    </div>
                    <div class="px-4 py-5 bg-gray-50 rounded-lg overflow-hidden sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">Penjualan Transfer</dt>
                        <dd class="mt-1 text-xl font-semibold text-gray-900">Rp {{ number_format($summary['sales_by_payment']['transfer'] ?? 0, 0, ',', '.') }}</dd>
                    </div>
                    <div class="px-4 py-5 bg-red-50 rounded-lg overflow-hidden sm:p-6">
                        <dt class="text-sm font-medium text-red-700 truncate">Penjualan Hutang</dt>
                        <dd class="mt-1 text-xl font-semibold text-red-800">Rp {{ number_format($summary['sales_by_payment']['debt'] ?? 0, 0, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Product Sales Details -->
            <div class="mt-8 overflow-auto">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Rincian Penjualan Produk pada {{ \Carbon\Carbon::parse($selectedDate)->format('d F Y') }}</h3>
                <div class="-mx-4 mt-4 overflow-x-auto shadow ring-1 ring-black ring-opacity-5 sm:-mx-6 md:mx-0 md:rounded-lg print:shadow-none print:ring-0">
                    <table class="min-w-full divide-y divide-gray-300 overflow-y-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Nama Produk</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Jml Terjual</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Harga Modal</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Harga Jual (Rata-rata)</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Penjualan</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Keuntungan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($productSalesData as $data)
                                <tr>
                                    <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $data['product_name'] }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-500 font-medium">{{ number_format($data['total_quantity']) }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-500">Rp {{ number_format($data['cost_price'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-500">Rp {{ number_format($data['avg_selling_price'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-500">Rp {{ number_format($data['total_sales'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-4 text-sm text-green-600 font-bold">Rp {{ number_format($data['total_profit'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada produk yang terjual pada tanggal ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
