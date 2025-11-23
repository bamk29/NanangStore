<div wire:poll.30s>
    <main class="py-4 main-content">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6">
                <!-- Penjualan Toko -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-4 sm:p-5">
                        <h3 class="text-sm font-medium text-gray-500 truncate">Penjualan Toko Hari Ini</h3>
                        <p class="mt-1 text-2xl font-semibold text-gray-900">Rp {{ number_format($nanangStoreTodaySales, 0, ',', '.') }}</p>
                    </div>
                </div>
                <!-- Penjualan Bakso -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-4 sm:p-5">
                        <h3 class="text-sm font-medium text-gray-500 truncate">Penjualan Bakso Hari Ini</h3>
                        <p class="mt-1 text-2xl font-semibold text-gray-900">Rp {{ number_format($baksoStoreTodaySales, 0, ',', '.') }}</p>
                    </div>
                </div>
                <!-- Uang Tunai di Laci -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-4 sm:p-5">
                        <h3 class="text-sm font-medium text-gray-500 truncate">Uang Tunai di Laci</h3>
                        <p class="mt-1 text-2xl font-semibold text-green-600">Rp {{ number_format($todayTotalCash, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 landscape-grid">

                <!-- Kasir Bersama -->
                <a href="{{ route('pos.index') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Kasir</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Proses transaksi</span>
                    </div>
                </a>

                <!-- Pesanan Masuk (BARU) -->
                <a href="{{ route('phone-orders.index') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-cyan-400 to-cyan-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Pesanan Masuk</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Dari telepon/WA</span>
                    </div>
                </a>

                <!-- Transaksi Tertunda (BARU) -->
                <a href="{{ route('transactions.pending') }}" wire:navigate class="relative bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    @if($pendingTransactionCount > 0)
                    <span class="absolute top-2 right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
                      {{ $pendingTransactionCount }}
                    </span>
                    @endif
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Transaksi Tertunda</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Lanjutkan pesanan</span>
                    </div>
                </a>

                <!-- Manajemen Pelanggan (BARU) -->
                <a href="{{ route('customers.index') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-teal-400 to-teal-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197m0 0A10.987 10.987 0 0112 13c2.43 0 4.616.64 6.44 1.703M12 13a10.987 10.987 0 00-6.44 1.703" /></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Data Pelanggan</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Kelola hutang & poin</span>
                    </div>
                </a>

                <!-- Produk -->
                <a href="{{ route('products.index') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Produk</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Kelola inventaris</span>
                    </div>
                </a>

                <!-- Penerimaan Barang -->
                <a href="{{ route('goods-receipts.index') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-cyan-400 to-cyan-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Penerimaan</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Terima dari PO</span>
                    </div>
                </a>

                <!-- Retur Barang -->
                <a href="{{ route('goods-returns.index') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-rose-400 to-rose-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                            </svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Retur</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Kembalikan barang</span>
                    </div>
                </a>

                <!-- Supplier -->
                <a href="{{ route('suppliers.index') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-gray-400 to-gray-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2V7a2 2 0 012-2h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293H17z"></path></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Supplier</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Kelola supplier</span>
                    </div>
                </a>

                <!-- Kategori -->
                <a href="{{ route('categories.index') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Kategori</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Kelola kategori</span>
                    </div>
                </a>

                @if(auth()->user()->isAdmin())
                <!-- Laporan Penjualan -->
                <a href="{{ route('reports.sales') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Laporan Penjualan</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Analisis penjualan</span>
                    </div>
                </a>

                <!-- Laporan Harian -->
                <a href="{{ route('reports.daily') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-teal-400 to-teal-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Laporan Harian</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Ringkasan harian</span>
                    </div>
                </a>

                <!-- Laporan Produk -->
                <a href="{{ route('reports.product') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Laporan Produk</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Performa produk</span>
                    </div>
                </a>

                <!-- Laporan Hutang -->
                <a href="{{ route('reports.debt') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.28-1.25-1.44-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.28-1.25 1.44-1.857M12 12a3 3 0 100-6 3 3 0 000 6z" /></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Laporan Hutang</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Daftar piutang</span>
                    </div>
                </a>

                <!-- Laporan Keuangan (Toko) -->
                <a href="{{ route('financials.nanang-store') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" /></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Lap. Keuangan (Toko)</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Kas & Saldo Toko</span>
                    </div>
                </a>

                <!-- Laporan Keuangan (Bakso) -->
                <a href="{{ route('financials.giling-bakso') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-lime-400 to-lime-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" /></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Lap. Keuangan (Bakso)</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Kas & Saldo Bakso</span>
                    </div>
                </a>

                <!-- Riwayat Transaksi -->
                <a href="{{ route('reports.transaction-history') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Riwayat Transaksi</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Cetak ulang struk</span>
                    </div>
                </a>

                <!-- Laporan Inventaris -->
                <a href="{{ route('reports.inventory') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-red-400 to-red-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Laporan Inventaris</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Tingkat stok</span>
                    </div>
                </a>
                @endif

                <!-- Purchase Orders -->
                <a href="{{ route('purchase-orders.index') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-gray-400 to-gray-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Purchase Order</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Belanja ke Supplier</span>
                    </div>
                </a>

                <!-- Produk Baru -->
                <a href="{{ route('products.create') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Produk Baru</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Tambah ke inventaris</span>
                    </div>
                </a>

                <!-- Ubah Harga Ayam -->
                <a href="{{ route('products.edit', 2) }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-pink-400 to-pink-600 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Ubah Harga Ayam</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Perbarui harga</span>
                    </div>
                </a>

                @if(auth()->user()->isAdmin())
                <!-- Laporan Penjualan (Bakso) -->
                <a href="{{ route('reports.sales-bakso') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-red-500 to-red-700 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v11.494m-9-5.747h18" /></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Lap. Penjualan (Bakso)</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Analisis penjualan bakso</span>
                    </div>
                </a>

                <!-- Laporan Penjualan (Toko) -->
                <a href="{{ route('reports.sales-nanang-store') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-amber-500 to-amber-700 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Lap. Penjualan (Toko)</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Analisis penjualan toko</span>
                    </div>
                </a>

                <!-- Laporan Profit Harian -->
                <a href="{{ route('reports.daily-profit') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-green-500 to-green-700 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Laporan Profit Harian</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Keuntungan per hari</span>
                    </div>
                </a>

                <!-- Transaksi Hari Ini -->
                <a href="{{ route('reports.today-transaction') }}" wire:navigate class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-14 h-14 landscape-icon bg-gradient-to-br from-cyan-500 to-cyan-700 rounded-xl flex items-center justify-center text-white shadow-inner group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <span class="font-medium text-gray-900 text-sm sm:text-base">Transaksi Hari Ini</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:block">Detail transaksi hari ini</span>
                    </div>
                </a>
                @endif
            </div>
        </div>
    </main>
</div>