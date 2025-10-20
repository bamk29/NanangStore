<div class="p-3 md:p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">💳 POS Transaksi</h1>
    </div>

    <div class="grid md:grid-cols-3 gap-4">
        <!-- Left: Produk List -->
        <div class="md:col-span-2">
            <!-- Filter & Search -->
            <div class="bg-white p-4 rounded-lg shadow space-y-4 mb-4">
                <input wire:model.live.debounce.300ms="search"
                    class="border rounded-lg px-3 py-2 w-full text-sm sm:text-base" placeholder="Cari produk...">

                <div x-data="{ showAll: false }">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <div class="flex-1 flex overflow-x-auto gap-2 pb-2 scrollbar-none">
                            <button wire:click="$set('categoryFilter', '')"
                                class="flex-none px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors {{ !$categoryFilter ? 'bg-blue-500 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                Semua
                            </button>
                            @foreach ($categories->take(5) as $category)
                                <button wire:click="$set('categoryFilter', '{{ $category->id }}')"
                                    class="flex-none px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors {{ $categoryFilter == $category->id ? 'bg-blue-500 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                    {{ $category->name }}
                                </button>
                            @endforeach
                        </div>
                        @if ($categories->count() > 5)
                            <button @click="showAll = !showAll" class="flex-none flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 hover:bg-gray-200">
                                <span>Lainnya</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showAll }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                        @endif
                    </div>
                    <div x-show="showAll" x-collapse class="mt-3 pt-3 border-t">
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-2">
                            @foreach ($categories as $category)
                                <button wire:click="$set('categoryFilter', '{{ $category->id }}')" class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $categoryFilter == $category->id ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                    {{ $category->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produk List -->
            <div wire:loading.remove class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                @foreach ($products as $product)
                    <div class="bg-white p-3 rounded-lg shadow hover:shadow-md transition cursor-pointer"
                        @click="$dispatch('add-to-cart', { product: {{ json_encode($product) }} })">
                        <p class="font-semibold text-sm sm:text-base line-clamp-2" title="{{ $product->name }}">{{ $product->name }}</p>
                        <p class="text-xs text-gray-500">{{ $product->code }}</p>
                        <p class="text-sm font-bold text-green-700 mt-1">
                            Rp {{ number_format($product->retail_price, 0, ',', '.') }}
                        </p>
                    </div>
                @endforeach
            </div>
            <div wire:loading.flex class="w-full items-center justify-center py-10">
                 <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <div class="mt-4">
                {{ $products->links() }}
            </div>
        </div>

        <!-- Right: Cart Section -->
        <div class="mt-5 md:mt-0">
            <livewire:kasir.pos-cart />
        </div>
    </div>
