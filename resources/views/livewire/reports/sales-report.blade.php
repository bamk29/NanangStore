<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header & Print Button -->
        <div class="flex justify-between items-center mb-6 print:hidden">
            <h1 class="text-2xl font-bold text-gray-900">Laporan Penjualan Lengkap</h1>
            <button onclick="window.print()" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-md flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak Laporan
            </button>
        </div>

        <!-- Filters (Hidden on Print) -->
        <div class="bg-white rounded-lg shadow p-6 mb-6 print:hidden">
            <!-- Quick Date Filters -->
            <div class="mb-4 flex flex-wrap gap-2">
                <button wire:click="setDateRange('today')" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-md transition-colors">Hari Ini</button>
                <button wire:click="setDateRange('week')" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-md transition-colors">Minggu Ini</button>
                <button wire:click="setDateRange('month')" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-md transition-colors">Bulan Ini</button>
                <button wire:click="setDateRange('year')" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-md transition-colors">Tahun Ini</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                    <input type="date" wire:model="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                    <input type="date" wire:model="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Toko</label>
                    <select wire:model="storeFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Toko</option>
                        <option value="nanang_store">Nanang Store</option>
                        <option value="bakso">Giling Bakso</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kategori</label>
                    <select wire:model="categoryFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Metode Pembayaran</label>
                    <select wire:model="paymentMethodFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Metode</option>
                        <option value="cash">Tunai</option>
                        <option value="transfer">Transfer</option>
                        <option value="debt">Kasbon</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipe Transaksi</label>
                    <select wire:model="transactionType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Tipe</option>
                        <option value="retail">Eceran</option>
                        <option value="wholesale">Grosir</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kelompokkan Berdasarkan</label>
                    <select wire:model="groupBy" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="date">Tanggal</option>
                        <option value="product">Produk</option>
                        <option value="category">Kategori</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Interval Grafik</label>
                    <select wire:model="chartInterval" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="daily">Harian</option>
                        <option value="weekly">Mingguan</option>
                        <option value="monthly">Bulanan</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button wire:click="applyFilter" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md w-full flex justify-center items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Terapkan Filter
                    </button>
                    <button wire:click="exportCsv" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md w-full flex justify-center items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <h3 class="text-sm font-medium text-gray-500">Total Penjualan</h3>
                <p class="mt-2 text-2xl font-bold text-gray-900">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ number_format($totalTransactions) }} Transaksi</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                <h3 class="text-sm font-medium text-gray-500">Total Modal (HPP)</h3>
                <p class="mt-2 text-2xl font-bold text-gray-900">Rp {{ number_format($totalCost, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <h3 class="text-sm font-medium text-gray-500">Total Keuntungan</h3>
                <p class="mt-2 text-2xl font-bold text-green-600">Rp {{ number_format($totalProfit, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                <h3 class="text-sm font-medium text-gray-500">Margin Keuntungan</h3>
                <p class="mt-2 text-2xl font-bold text-purple-600">
                    {{ $totalSales > 0 ? number_format(($totalProfit / $totalSales) * 100, 1) : 0 }}%
                </p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="bg-white rounded-lg shadow p-6 mb-6 print:break-inside-avoid">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Grafik Tren Penjualan & Keuntungan</h3>
            <div x-data="salesChart(@js($chartData))" 
                 x-init="initChart()" 
                 wire:ignore 
                 class="w-full">
                <div x-ref="chartContainer" style="min-height: 350px;"></div>
            </div>
        </div>

        <!-- Sales Data Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden print:shadow-none">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                @if($groupBy === 'date') Tanggal
                                @elseif($groupBy === 'product') Produk
                                @else Kategori
                                @endif
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Penjualan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Modal</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Keuntungan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Margin</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                            @if($groupBy === 'date')
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Transaksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($salesData as $data)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $data['key'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">Rp {{ number_format($data['total_sales'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">Rp {{ number_format($data['total_cost'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600 font-medium">Rp {{ number_format($data['total_profit'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                                    {{ $data['total_sales'] > 0 ? number_format(($data['total_profit'] / $data['total_sales']) * 100, 1) : 0 }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($data['quantity'], 0, ',', '.') }}</td>
                                @if($groupBy === 'date')
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">{{ $data['transaction_count'] }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-gray-500">Tidak ada data penjualan untuk filter yang dipilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 font-bold">
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">TOTAL</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-900">Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-500">Rp {{ number_format($totalCost, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-sm text-right text-green-600">Rp {{ number_format($totalProfit, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-500">
                                {{ $totalSales > 0 ? number_format(($totalProfit / $totalSales) * 100, 1) : 0 }}%
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-gray-900">{{ number_format($salesData->sum('quantity'), 0, ',', '.') }}</td>
                            @if($groupBy === 'date')
                                <td class="px-6 py-4 text-sm text-right text-gray-900">{{ number_format($totalTransactions) }}</td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- ApexCharts -->
    <!-- ApexCharts -->
    <script>
        function salesChart(initialData) {
            return {
                chart: null,
                data: initialData,
                initChart() {
                    if (this.chart) {
                        this.chart.destroy();
                    }

                    const options = this.getOptions(this.data);
                    this.chart = new ApexCharts(this.$refs.chartContainer, options);
                    this.chart.render();

                    // Listen for updates from Livewire
                    Livewire.on('chart-updated', (data) => {
                        const chartData = Array.isArray(data) ? data[0] : data;
                        this.updateChart(chartData);
                    });
                },
                updateChart(newData) {
                    this.data = newData;
                    if (this.chart) {
                        this.chart.updateOptions(this.getOptions(newData));
                    }
                },
                getOptions(data) {
                    return {
                        series: [{
                            name: 'Penjualan',
                            data: data.sales
                        }, {
                            name: 'Keuntungan',
                            data: data.profit
                        }, {
                            name: 'Transaksi',
                            data: data.transactions
                        }],
                        chart: {
                            height: 350,
                            type: 'bar',
                            toolbar: { show: false },
                            fontFamily: 'inherit'
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '55%',
                                endingShape: 'rounded',
                                borderRadius: 4
                            },
                        },
                        dataLabels: { enabled: false },
                        stroke: {
                            show: true,
                            width: 2,
                            colors: ['transparent']
                        },
                        xaxis: {
                            categories: data.labels,
                            type: 'category',
                            labels: {
                                style: { colors: '#6B7280', fontSize: '12px' }
                            }
                        },
                        yaxis: [
                            {
                                seriesName: 'Penjualan',
                                labels: {
                                    style: { colors: '#3B82F6', fontSize: '12px' },
                                    formatter: (value) => {
                                        return new Intl.NumberFormat('id-ID', { 
                                            style: 'currency', 
                                            currency: 'IDR', 
                                            maximumSignificantDigits: 3,
                                            notation: 'compact'
                                        }).format(value);
                                    }
                                },
                                title: {
                                    text: "Nominal (Rp)",
                                    style: { color: '#3B82F6' }
                                }
                            },
                            {
                                seriesName: 'Penjualan',
                                show: false,
                                labels: {
                                    formatter: (value) => {
                                        return new Intl.NumberFormat('id-ID', { 
                                            style: 'currency', 
                                            currency: 'IDR', 
                                            maximumSignificantDigits: 3,
                                            notation: 'compact'
                                        }).format(value);
                                    }
                                }
                            },
                            {
                                opposite: true,
                                seriesName: 'Transaksi',
                                labels: {
                                    style: { colors: '#F59E0B', fontSize: '12px' }
                                },
                                title: {
                                    text: "Jumlah Transaksi",
                                    style: { color: '#F59E0B' }
                                }
                            }
                        ],
                        tooltip: {
                            theme: 'light',
                            y: {
                                formatter: (value, { seriesIndex, w }) => {
                                    if (seriesIndex === 2) { // Transaksi
                                        return value + ' Transaksi';
                                    }
                                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
                                }
                            }
                        },
                        colors: ['#3B82F6', '#10B981', '#F59E0B'],
                        fill: {
                            opacity: 1
                        },
                        grid: {
                            borderColor: '#F3F4F6',
                            strokeDashArray: 4,
                        }
                    };
                }
            }
        }
    </script>
    
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .py-6, .py-6 * {
                visibility: visible;
            }
            .py-6 {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .print\:hidden {
                display: none !important;
            }
            .shadow {
                box-shadow: none !important;
                border: 1px solid #ddd;
            }
        }
    </style>
</div>
