<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NANANGStore</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
        .glass-nav {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }
        .glass-sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
        }
        .nav-item-active {
            background: linear-gradient(to right, #eff6ff, #ffffff);
            border-left: 3px solid #3b82f6;
            color: #2563eb;
        }
        .nav-item-inactive {
            color: #64748b;
        }
        .nav-item-inactive:hover {
            background-color: #f8fafc;
            color: #1e293b;
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
<body class="antialiased bg-slate-50">

    <div class="min-h-screen bg-slate-50" x-data>
        <!-- Top Navigation (Glassmorphism) -->
        <div class="fixed top-0 left-0 right-0 z-50 glass-nav no-print transition-all duration-300">
            <div class="flex items-center justify-between h-16 px-4 max-w-7xl mx-auto">
                <div class="flex items-center">
                    <!-- Back Button (Top) - Optional if we have bottom back, but good to keep for consistency or remove if redundant. User asked for bottom back. Let's keep top back as "Home" or just logo if on dashboard. Actually, let's keep it as is for now. -->
                    <!-- Back Button (Top) -->
                    <button onclick="window.location.href = '{{ route('dashboard') }}'"
                        class="flex items-center justify-center w-12 h-12 rounded-full bg-white border-2 border-slate-300 hover:bg-slate-100 hover:border-slate-400 active:scale-95 transition-all duration-200 mr-4 text-slate-700 hover:text-slate-900 shadow-sm">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <!-- Title -->
                    <div class="flex flex-col">
                        <h1 class="text-lg font-bold text-slate-800 tracking-tight leading-tight">
                            {{ request()->routeIs('dashboard') ? config('app.name') : '' }}
                            {{ request()->routeIs('pos.*') ? 'Point of Sale' : '' }}
                            {{ request()->routeIs('products.*') ? 'Products' : '' }}
                            {{ request()->routeIs('categories.*') ? 'Categories' : '' }}
                            {{ request()->routeIs('reports.*') ? 'Reports' : '' }}
                            {{ request()->routeIs('customers.*') ? 'Customers' : '' }}
                            {{ request()->routeIs('transactions.*') ? 'Transactions' : '' }}
                            {{ request()->routeIs('purchase-orders.*') ? 'Purchase Orders' : '' }}
                            {{ request()->routeIs('goods-receipts.*') ? 'Goods Receipts' : '' }}
                        </h1>
                        @if(!request()->routeIs('dashboard'))
                        <span class="text-xs text-slate-500 font-medium">NanangStore System</span>
                        @endif
                    </div>
                </div>

                <!-- Logout Button (Replaces Menu Button) -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="p-2 rounded-full text-red-500 hover:bg-red-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-200"
                        onclick="return confirm('Apakah anda yakin ingin keluar?')">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden pt-16 print:overflow-visible" :class="$store.ui.isBottomNavVisible ? 'pb-32' : 'pb-4'">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 print:overflow-visible">
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
                        
                        <!-- Home -->
                        <a href="{{ route('dashboard') }}"
                            class="flex flex-col items-center p-2 relative group w-1/3">
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

                        <!-- POS -->
                        <a href="{{ route('pos.index') }}"
                            class="flex flex-col items-center p-2 relative group w-1/3">
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

                        <!-- Back -->
                        <button onclick="window.history.back()"
                            class="flex flex-col items-center p-2 relative group w-1/3">
                            <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-gray-100 group-hover:bg-gray-200 transition-all duration-300">
                                <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                            </div>
                            <span class="text-xs mt-1 font-medium text-gray-600">Kembali</span>
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
