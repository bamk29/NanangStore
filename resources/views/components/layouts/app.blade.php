<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NANANGStore</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        .android-nav-bar {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        /* Landscape mode optimizations */
        @media (orientation: landscape) and (max-height: 640px) {
            .landscape-optimize { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }
            .landscape-nav { height: 3rem !important; }
            .landscape-content { height: calc(100vh - 3rem) !important; padding-bottom: 3.5rem !important; }
            .landscape-bottom-nav { height: 3.5rem !important; }
            .landscape-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important; }
        }
    </style>
</head>
<body class="antialiased bg-gray-100">

    <div class="min-h-screen bg-gray-100" x-data="{ mobileMenu: false }">
        <!-- Android-style Top Navigation -->
        <div class="fixed top-0 left-0 right-0 z-50 android-nav-bar bg-white bg-opacity-95 no-print">
            <div class="flex items-center h-16 landscape-nav px-4 shadow-[0_1px_3px_0_rgba(0,0,0,0.1)] rounded-b-xl">
                <!-- Back Button -->
                <button onclick="window.location.href = '{{ route('dashboard') }}'"
                    class="flex items-center p-2 rounded-xl bg-gray-100 hover:bg-gray-200 active:scale-95 transition-all duration-150">
                    <div class="w-8 h-8 flex items-center justify-center">
                        <svg class="h-5 w-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </div>
                    <span class="ml-1 text-sm font-medium text-gray-700">Kembali</span>
                </button>

                <!-- Title with gradient background based on route -->
                <div class="ml-4 py-1 px-4 rounded-xl
                    {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-blue-500 to-blue-600' : '' }}
                    {{ request()->routeIs('pos.*') ? 'bg-gradient-to-r from-green-500 to-green-600' : '' }}
                    {{ request()->routeIs('products.*') ? 'bg-gradient-to-r from-purple-500 to-purple-600' : '' }}
                    {{ request()->routeIs('categories.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600' : '' }}
                    {{ request()->routeIs('reports.*') ? 'bg-gradient-to-r from-red-500 to-red-600' : '' }}">
                    <h1 class="text-lg font-semibold {{ request()->routeIs('dashboard') || request()->routeIs('pos.*') || request()->routeIs('products.*') || request()->routeIs('categories.*') || request()->routeIs('reports.*') ? 'text-white' : 'text-gray-800' }}">
                        {{ request()->routeIs('dashboard') ? config('app.name') : '' }}
                        {{ request()->routeIs('pos.*') ? 'Point of Sale' : '' }}
                        {{ request()->routeIs('products.*') ? 'Products' : '' }}
                        {{ request()->routeIs('categories.*') ? 'Categories' : '' }}
                        {{ request()->routeIs('reports.*') ? 'Reports' : '' }}
                    </h1>
                </div>                <!-- Menu Button -->
                <button @click="mobileMenu = !mobileMenu"
                    class="ml-auto p-2 rounded-full hover:bg-gray-100">

                </button>
            </div>
        </div>

        <!-- Sidebar -->
        <div x-data="{ mobileMenu: false }" class="flex h-screen bg-gray-100 print:h-auto" x-cloak>
            <div class="fixed inset-0 z-20 transition-opacity bg-black opacity-50 lg:hidden"
                x-show="mobileMenu"
                @click="mobileMenu = false">
            </div>

            <div class="fixed inset-y-0 right-0 z-50 w-64 overflow-y-auto transition duration-300 transform bg-white shadow-lg"
                :class="{'translate-x-0 ease-out': mobileMenu, 'translate-x-full ease-in': !mobileMenu}">

                <!-- User Profile -->
                <div class="p-4 bg-gray-50 border-b">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <span class="text-blue-600 font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500">Cashier</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="p-4">
                    <div class="space-y-1">
                        <!-- Link Pelanggan -->
                        <a class="flex items-center px-4 py-2 {{ request()->routeIs('customers.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-500 hover:bg-gray-100' }} rounded-lg"
                            href="{{ route('customers.index') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197m0 0A10.987 10.987 0 0112 13c2.43 0 4.616.64 6.44 1.703M12 13a10.987 10.987 0 00-6.44 1.703" /></svg>
                            <span class="mx-4">Data Pelanggan</span>
                        </a>

                        <!-- Link Transaksi Tertunda -->
                        <a class="flex items-center px-4 py-2 {{ request()->routeIs('transactions.pending') ? 'bg-gray-100 text-blue-600' : 'text-gray-500 hover:bg-gray-100' }} rounded-lg"
                            href="{{ route('transactions.pending') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span class="mx-4">Transaksi Tertunda</span>
                        </a>

                        <a class="flex items-center px-4 py-2 {{ request()->routeIs('categories.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-500 hover:bg-gray-100' }} rounded-lg"
                            href="{{ route('categories.index') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <span class="mx-4">Kategori</span>
                        </a>

                        <!-- Reports Section -->
                        <div x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }" class="space-y-1">
                            <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-2 {{ request()->routeIs('reports.*') ? 'bg-gray-100 text-blue-600' : 'text-gray-500 hover:bg-gray-100' }} rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="mx-4">Reports</span>
                                </div>
                                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" class="pl-10 space-y-1">
                                <a href="{{ route('reports.sales') }}"
                                    class="flex items-center px-4 py-2 {{ request()->routeIs('reports.sales') ? 'bg-gray-100 text-blue-600' : 'text-gray-500 hover:bg-gray-100' }} rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                    <span class="mx-4">Sales Report</span>
                                </a>
                                <a href="{{ route('reports.inventory') }}"
                                    class="flex items-center px-4 py-2 {{ request()->routeIs('reports.inventory') ? 'bg-gray-100 text-blue-600' : 'text-gray-500 hover:bg-gray-100' }} rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <span class="mx-4">Inventory Report</span>
                                </a>
                                <a href="{{ route('reports.transaction') }}"
                                    class="flex items-center px-4 py-2 {{ request()->routeIs('reports.transaction') ? 'bg-gray-100 text-blue-600' : 'text-gray-500 hover:bg-gray-100' }} rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="mx-4">Transaction Report</span>
                                </a>
                                <a href="{{ route('financials.nanang-store') }}"
                                    class="flex items-center px-4 py-2 {{ request()->routeIs('financials.nanang-store') ? 'bg-gray-100 text-blue-600' : 'text-gray-500 hover:bg-gray-100' }} rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
                                    <span class="mx-4">Lap. Keuangan (Toko)</span>
                                </a>
                                <a href="{{ route('financials.giling-bakso') }}"
                                    class="flex items-center px-4 py-2 {{ request()->routeIs('financials.giling-bakso') ? 'bg-gray-100 text-blue-600' : 'text-gray-500 hover:bg-gray-100' }} rounded-lg">
                                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
                                    <span class="mx-4">Lap. Keuangan (Bakso)</span>
                                </a>
                            </div>
                        </div>

                        @if(auth()->user()->isAdmin())
                        <a class="flex items-center px-4 py-2 {{ request()->routeIs('users.index') ? 'bg-gray-100 text-blue-600' : 'text-gray-500 hover:bg-gray-100' }} rounded-lg"
                            href="{{ route('users.index') }}" wire:navigate>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.28-1.25-1.44-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.28-1.25 1.44-1.857M12 12a3 3 0 100-6 3 3 0 000 6z" /></svg>
                            <span class="mx-4">Manajemen User</span>
                        </a>
                        @endif
                    </div>

                    <div class="mt-auto pt-4 border-t">
                        <form method="POST" action="{{ route('logout') }}" class="px-4">
                            @csrf
                            <button type="submit" class="flex items-center px-4 py-2 w-full text-left text-red-600 hover:bg-red-50 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span class="mx-4">Logout</span>
                            </button>
                        </form>
                    </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col overflow-hidden pt-14 print:overflow-visible" :class="$store.ui.isBottomNavVisible ? 'pb-24' : 'pb-4'"> <!-- Added pb-24 for bottom navigation space -->
                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 print:overflow-visible">
                    {{ $slot }}
                </main>

                <!-- Android-style Bottom Navigation -->
                <div x-show="$store.ui.isBottomNavVisible"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="transform translate-y-full"
                     x-transition:enter-end="transform translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="transform translate-y-0"
                     x-transition:leave-end="transform translate-y-full"
                     class="fixed bottom-0 left-0 right-0 z-50 android-nav-bar bg-white bg-opacity-95 no-print">
                    <div class="flex justify-around items-center px-2 py-2 shadow-[0_-8px_16px_-6px_rgba(0,0,0,0.1)] rounded-t-2xl border-t border-gray-200">
                        <a href="{{ route('dashboard') }}"
                            class="flex flex-col items-center p-2 relative group">
                            <div class="w-12 h-12 landscape:w-10 landscape:h-10 flex items-center justify-center rounded-xl {{ request()->routeIs('dashboard')
                                ? 'bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/50'
                                : 'bg-gray-100 group-hover:bg-gray-200' }} transition-all duration-300">
                                <svg class="h-6 w-6 landscape:h-5 landscape:w-5 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-600' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                            <span class="text-xs mt-1 font-medium {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-600' }}">Home</span>
                        </a>

                        <a href="{{ route('pos.index') }}"
                            class="flex flex-col items-center p-2 relative group">
                            <div class="w-12 h-12 flex items-center justify-center rounded-xl {{ request()->routeIs('pos.*')
                                ? 'bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/50'
                                : 'bg-gray-100 group-hover:bg-gray-200' }} transition-all duration-300">
                                <svg class="h-6 w-6 {{ request()->routeIs('pos.*') ? 'text-white' : 'text-gray-600' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="text-xs mt-1 font-medium {{ request()->routeIs('pos.*') ? 'text-green-600' : 'text-gray-600' }}">POS</span>
                        </a>

                        <a href="{{ route('products.index') }}"
                            class="flex flex-col items-center p-2 relative group">
                            <div class="w-12 h-12 flex items-center justify-center rounded-xl {{ request()->routeIs('products.*')
                                ? 'bg-gradient-to-br from-purple-500 to-purple-600 shadow-lg shadow-purple-500/50'
                                : 'bg-gray-100 group-hover:bg-gray-200' }} transition-all duration-300">
                                <svg class="h-6 w-6 {{ request()->routeIs('products.*') ? 'text-white' : 'text-gray-600' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <span class="text-xs mt-1 font-medium {{ request()->routeIs('products.*') ? 'text-purple-600' : 'text-gray-600' }}">Products</span>
                        </a>

                        <button @click="mobileMenu = !mobileMenu"
                            class="flex flex-col items-center p-2 relative group">
                            <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-gray-100 group-hover:bg-gray-200 transition-all duration-300">
                                <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </div>
                            <span class="text-xs mt-1 font-medium text-gray-600">More</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

                    @livewireScripts    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('ui', {
                isBottomNavVisible: true,
            });
        });

        document.addEventListener('livewire:initialized', () => {
            // Close mobile menu when navigating
            Livewire.on('navigated', () => {
                Alpine.store('mobileMenu', false);
            });

            // Listener untuk event print
            window.addEventListener('printInvoice', event => {
                window.print();
            });

            Livewire.on('open-new-tab', url => {
                window.open(url, '_blank');
            });
        });
    </script>
    <script>
        document.addEventListener('livewire:init', () => {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            // Listener untuk event dari backend
            window.addEventListener('show-alert', event => {
                const detail = event.detail;
                // Logika fallback untuk menangani berbagai struktur data
                const message = detail.message || (detail[0] ? detail[0].message : 'Pesan tidak ditemukan.');
                const type = detail.type || (detail[0] ? detail[0].type : 'success');

                Toast.fire({
                    icon: type,
                    title: message
                });
            });
        });
    </script>
</body>
</html>
