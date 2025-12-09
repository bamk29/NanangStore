<div wire:poll.30s class="min-h-screen bg-slate-50/50">
    <!-- Hero Section -->
    <div class="bg-white border-b border-slate-200 sticky top-0 z-30 lg:static lg:z-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">
                        Selamat Datang, {{ auth()->user()->name }}! 👋
                    </h1>
                    <p class="mt-1 text-slate-500 text-sm sm:text-base">
                        {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }} &bull; <span class="font-medium text-blue-600">Dashboard Overview</span>
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('pos.index') }}" wire:navigate class="inline-flex items-center justify-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-2xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg shadow-blue-500/30 transition-all duration-200 active:scale-95">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Buka Kasir
                    </a>
                </div>
            </div>
        </div>
    </div>

    <main class="py-6 main-content">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Penjualan Toko -->
                <div class="relative overflow-hidden bg-white rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 p-6 group hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] transition-all duration-300">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-blue-500 rounded-full opacity-10 blur-2xl group-hover:scale-110 transition-transform duration-500"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-6">
                            <a href="{{ route('reports.daily', ['storeFilter' => 'nanang_store']) }}" wire:navigate class="p-3 bg-blue-500 rounded-2xl text-white shadow-[0_10px_20px_rgba(59,130,246,0.5)] ring-4 ring-blue-100 border-2 border-white group-hover:bg-blue-900 transition-colors duration-300 cursor-pointer relative z-20">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </a>
                            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full border border-blue-100">+Hari Ini</span>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Penjualan Toko</h3>
                        <p class="mt-1 text-3xl font-bold text-slate-800 tracking-tight">Rp {{ number_format($nanangStoreTodaySales, 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- Penjualan Bakso -->
                <div class="relative overflow-hidden bg-white rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 p-6 group hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] transition-all duration-300">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-orange-500 rounded-full opacity-10 blur-2xl group-hover:scale-110 transition-transform duration-500"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-6">
                            <a href="{{ route('reports.daily', ['storeFilter' => 'bakso']) }}" wire:navigate class="p-3 bg-orange-500 rounded-2xl text-white shadow-[0_10px_20px_rgba(249,115,22,0.5)] ring-4 ring-orange-100 border-2 border-white group-hover:bg-orange-900 transition-colors duration-300 cursor-pointer relative z-20">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v11.494m-9-5.747h18"/></svg>
                            </a>
                            <span class="text-xs font-bold text-orange-600 bg-orange-50 px-3 py-1.5 rounded-full border border-orange-100">+Hari Ini</span>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Penjualan Bakso</h3>
                        <p class="mt-1 text-3xl font-bold text-slate-800 tracking-tight">Rp {{ number_format($baksoStoreTodaySales, 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- Uang Tunai -->
                <div class="relative overflow-hidden bg-white rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 p-6 group hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] transition-all duration-300">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-emerald-500 rounded-full opacity-10 blur-2xl group-hover:scale-110 transition-transform duration-500"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-6">
                            <div class="p-3 bg-emerald-500 rounded-2xl text-white shadow-[0_10px_20px_rgba(16,185,129,0.5)] ring-4 ring-emerald-100 border-2 border-white group-hover:bg-emerald-900 transition-colors duration-300">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-full border border-emerald-100">Cash Drawer</span>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Uang Tunai di Laci</h3>
                        <p class="mt-1 text-3xl font-bold text-emerald-600 tracking-tight">Rp {{ number_format($todayTotalCash, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Operasional Utama -->
            <section>
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-1.5 h-6 bg-blue-600 rounded-full shadow-sm"></div>
                    <h2 class="text-xl font-bold text-slate-800 tracking-tight">Operasional Utama</h2>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6">
                    <!-- Kasir -->
                    <a href="{{ route('pos.index') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-blue-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-blue-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(59,130,246,0.6)] group-hover:scale-110 group-hover:bg-blue-900 transition-all duration-300 ring-4 ring-blue-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-blue-600 transition-colors">Kasir</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Proses transaksi</p>
                            </div>
                        </div>
                    </a>

                    <!-- Pesanan Masuk -->
                    <a href="{{ route('phone-orders.index') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-cyan-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-cyan-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(6,182,212,0.6)] group-hover:scale-110 group-hover:bg-cyan-900 transition-all duration-300 ring-4 ring-cyan-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-cyan-600 transition-colors">Pesanan Masuk</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Dari telepon/WA</p>
                            </div>
                        </div>
                    </a>

                    <!-- Transaksi Tertunda -->
                    <a href="{{ route('transactions.pending') }}" wire:navigate class="relative group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-orange-300 hover:bg-slate-50 transition-all duration-300 overflow-hidden">
                        @if($pendingTransactionCount > 0)
                        <span class="absolute top-3 right-3 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-[11px] font-bold text-white shadow-md ring-2 ring-white z-20">
                            {{ $pendingTransactionCount }}
                        </span>
                        @endif
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-orange-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(249,115,22,0.6)] group-hover:scale-110 group-hover:bg-orange-900 transition-all duration-300 ring-4 ring-orange-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-orange-600 transition-colors">Tertunda</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Lanjutkan pesanan</p>
                            </div>
                        </div>
                    </a>

                    <!-- Riwayat Transaksi -->
                    <a href="{{ route('reports.transaction-history') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-indigo-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-indigo-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(99,102,241,0.6)] group-hover:scale-110 group-hover:bg-indigo-900 transition-all duration-300 ring-4 ring-indigo-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">Riwayat</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Cetak ulang struk</p>
                            </div>
                        </div>
                    </a>
                </div>
            </section>

            <!-- Manajemen Data -->
            <section>
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-1.5 h-6 bg-purple-600 rounded-full shadow-sm"></div>
                    <h2 class="text-xl font-bold text-slate-800 tracking-tight">Manajemen Data</h2>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6">
                    <!-- Produk -->
                    <a href="{{ route('products.index') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-purple-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-purple-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(168,85,247,0.6)] group-hover:scale-110 group-hover:bg-purple-900 transition-all duration-300 ring-4 ring-purple-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-purple-600 transition-colors">Produk</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Kelola inventaris</p>
                            </div>
                        </div>
                    </a>

                    <!-- Kategori -->
                    <a href="{{ route('categories.index') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-pink-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-pink-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(236,72,153,0.6)] group-hover:scale-110 group-hover:bg-pink-900 transition-all duration-300 ring-4 ring-pink-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-pink-600 transition-colors">Kategori</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Kelola kategori</p>
                            </div>
                        </div>
                    </a>

                    <!-- Pelanggan -->
                    <a href="{{ route('customers.index') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-teal-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-teal-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(20,184,166,0.6)] group-hover:scale-110 group-hover:bg-teal-900 transition-all duration-300 ring-4 ring-teal-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197m0 0A10.987 10.987 0 0112 13c2.43 0 4.616.64 6.44 1.703M12 13a10.987 10.987 0 00-6.44 1.703" /></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-teal-600 transition-colors">Pelanggan</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Hutang & Poin</p>
                            </div>
                        </div>
                    </a>

                    <!-- Supplier -->
                    <a href="{{ route('suppliers.index') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-gray-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-gray-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(107,114,128,0.6)] group-hover:scale-110 group-hover:bg-gray-900 transition-all duration-300 ring-4 ring-gray-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2V7a2 2 0 012-2h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293H17z"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-gray-600 transition-colors">Supplier</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Kelola supplier</p>
                            </div>
                        </div>
                    </a>

                    <!-- Produk Baru -->
                    <a href="{{ route('products.create') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-indigo-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-indigo-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(99,102,241,0.6)] group-hover:scale-110 group-hover:bg-indigo-900 transition-all duration-300 ring-4 ring-indigo-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">Produk Baru</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Tambah item</p>
                            </div>
                        </div>
                    </a>

                    <!-- Import Produk -->
                    <a href="{{ route('products.import') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-violet-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-violet-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(139,92,246,0.6)] group-hover:scale-110 group-hover:bg-violet-900 transition-all duration-300 ring-4 ring-violet-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-violet-600 transition-colors">Import</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Upload Excel</p>
                            </div>
                        </div>
                    </a>

                    <!-- Manajemen User -->
                    <a href="{{ route('users.index') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-slate-400 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-slate-600 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(71,85,105,0.6)] group-hover:scale-110 group-hover:bg-slate-900 transition-all duration-300 ring-4 ring-slate-200 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197m0 0A10.987 10.987 0 0112 13c2.43 0 4.616.64 6.44 1.703M12 13a10.987 10.987 0 00-6.44 1.703"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-slate-700 transition-colors">Users</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Kelola pengguna</p>
                            </div>
                        </div>
                    </a>
                </div>
            </section>

            <!-- Stok & Logistik -->
            <section>
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-1.5 h-6 bg-amber-500 rounded-full shadow-sm"></div>
                    <h2 class="text-xl font-bold text-slate-800 tracking-tight">Stok & Logistik</h2>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6">
                    <!-- Purchase Orders -->
                    <a href="{{ route('purchase-orders.index') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-amber-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(245,158,11,0.6)] group-hover:scale-110 group-hover:bg-amber-900 transition-all duration-300 ring-4 ring-amber-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-amber-600 transition-colors">Purchase Order</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Belanja supplier</p>
                            </div>
                        </div>
                    </a>

                    <!-- Penerimaan -->
                    <a href="{{ route('goods-receipts.index') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-cyan-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-cyan-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(6,182,212,0.6)] group-hover:scale-110 group-hover:bg-cyan-900 transition-all duration-300 ring-4 ring-cyan-100 border-2 border-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-cyan-600 transition-colors">Penerimaan</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Terima dari PO</p>
                            </div>
                        </div>
                    </a>

                    <!-- Retur -->
                    <a href="{{ route('goods-returns.index') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-rose-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-rose-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(244,63,94,0.6)] group-hover:scale-110 group-hover:bg-rose-900 transition-all duration-300 ring-4 ring-rose-100 border-2 border-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-rose-600 transition-colors">Retur</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Kembalikan barang</p>
                            </div>
                        </div>
                    </a>

                    <!-- Penyesuaian Stok -->
                    <a href="{{ route('stock-adjustments.index') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-emerald-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(16,185,129,0.6)] group-hover:scale-110 group-hover:bg-emerald-900 transition-all duration-300 ring-4 ring-emerald-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">Stok Opname</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Sesuaikan stok</p>
                            </div>
                        </div>
                    </a>

                    <!-- Penyesuaian Harga -->
                    <a href="{{ route('price-adjustments.index') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-yellow-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-yellow-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(234,179,8,0.6)] group-hover:scale-110 group-hover:bg-yellow-900 transition-all duration-300 ring-4 ring-yellow-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-yellow-600 transition-colors">Atur Harga</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Update harga</p>
                            </div>
                        </div>
                    </a>

                    <!-- Monitor Stok -->
                    <a href="{{ route('inventory.low-stock') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-red-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-red-600 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(220,38,38,0.6)] group-hover:scale-110 group-hover:bg-red-900 transition-all duration-300 ring-4 ring-red-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-red-600 transition-colors">Monitor Stok</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Stok menipis</p>
                            </div>
                        </div>
                    </a>
                </div>
            </section>

            @if(auth()->user()->isAdmin())
            <!-- Laporan & Analisis -->
            <section>
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-1.5 h-6 bg-red-600 rounded-full shadow-sm"></div>
                    <h2 class="text-xl font-bold text-slate-800 tracking-tight">Laporan & Analisis</h2>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6">
                    <!-- Laporan Penjualan -->
                    <a href="{{ route('reports.sales') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-blue-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-blue-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(59,130,246,0.6)] group-hover:scale-110 group-hover:bg-blue-900 transition-all duration-300 ring-4 ring-blue-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-blue-600 transition-colors">Penjualan</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Laporan umum</p>
                            </div>
                        </div>
                    </a>

                    <!-- Laporan Harian -->
                    <a href="{{ route('reports.daily') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-teal-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-teal-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(20,184,166,0.6)] group-hover:scale-110 group-hover:bg-teal-900 transition-all duration-300 ring-4 ring-teal-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-teal-600 transition-colors">Harian</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Ringkasan hari ini</p>
                            </div>
                        </div>
                    </a>

                    <!-- Laporan Profit -->
                    <a href="{{ route('reports.daily-profit') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-green-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-green-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(34,197,94,0.6)] group-hover:scale-110 group-hover:bg-green-900 transition-all duration-300 ring-4 ring-green-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-green-600 transition-colors">Profit</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Keuntungan</p>
                            </div>
                        </div>
                    </a>

                    <!-- Laporan Transaksi -->
                    <a href="{{ route('reports.transaction') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-purple-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-purple-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(168,85,247,0.6)] group-hover:scale-110 group-hover:bg-purple-900 transition-all duration-300 ring-4 ring-purple-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-purple-600 transition-colors">Transaksi</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Riwayat lengkap</p>
                            </div>
                        </div>
                    </a>

                    <!-- Laporan Inventaris -->
                    <a href="{{ route('reports.inventory') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-red-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-red-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(239,68,68,0.6)] group-hover:scale-110 group-hover:bg-red-900 transition-all duration-300 ring-4 ring-red-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-red-600 transition-colors">Inventaris</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Stok barang</p>
                            </div>
                        </div>
                    </a>

                    <!-- Laporan Hutang -->
                    <a href="{{ route('reports.debt') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-orange-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-orange-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(249,115,22,0.6)] group-hover:scale-110 group-hover:bg-orange-900 transition-all duration-300 ring-4 ring-orange-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.28-1.25-1.44-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.28-1.25 1.44-1.857M12 12a3 3 0 100-6 3 3 0 000 6z" /></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-orange-600 transition-colors">Hutang</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Piutang pelanggan</p>
                            </div>
                        </div>
                    </a>

                    <!-- Laporan Keuangan Toko -->
                    <a href="{{ route('financials.nanang-store') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-emerald-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(16,185,129,0.6)] group-hover:scale-110 group-hover:bg-emerald-900 transition-all duration-300 ring-4 ring-emerald-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">Keuangan Toko</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Laporan lengkap</p>
                            </div>
                        </div>
                    </a>

                    <!-- Laporan Keuangan Bakso -->
                    <a href="{{ route('financials.giling-bakso') }}" wire:navigate class="group bg-white p-5 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.08)] border border-slate-200 hover:shadow-[0_15px_40px_rgb(0,0,0,0.12)] hover:border-orange-300 hover:bg-slate-50 transition-all duration-300 relative overflow-hidden">
                        <div class="flex flex-col items-center text-center space-y-4 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-orange-500 text-white flex items-center justify-center shadow-[0_10px_25px_rgba(249,115,22,0.6)] group-hover:scale-110 group-hover:bg-orange-900 transition-all duration-300 ring-4 ring-orange-100 border-2 border-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-orange-600 transition-colors">Keuangan Bakso</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Laporan lengkap</p>
                            </div>
                        </div>
                    </a>
                </div>
            </section>
            @endif

        </div>
    </main>
</div>