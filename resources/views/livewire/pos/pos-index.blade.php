<div class="p-3 md:p-6 bg-gray-50 min-h-screen">
    <!-- Main Content -->
    <div class="flex-1 flex flex-col md:flex-row">
        <!-- Products Section -->
        <div class="flex-1 md:w-2/3 flex flex-col">
            <!-- Fixed Search Section -->
             <div class="bg-white p-4 rounded-lg shadow space-y-4 mb-4 mx-2 my-2">
                <input wire:model.live.debounce.300ms="search"
                    class="border rounded-lg px-3 py-2 w-full text-sm sm:text-base" placeholder="Cari produk...">

                <div x-data="{ showAll: false }">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <div class="flex-1 flex overflow-x-auto gap-2 pb-2 scrollbar-none">
                            <button wire:click="$set('categoryFilter', '')"
                                class="flex-none px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors {{ !$categoryFilter ? 'bg-blue-500 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                Semua
                            </button>

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

            <!-- Scrollable Products Grid -->
            <div class="flex-1 overflow-y-auto bg-gray-50 px-2 pt-2 pb-safe">
                <div
                    class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 auto-rows-fr">
                    @foreach ($products as $product)
                        <div x-data="{ product: JSON.parse('{{ json_encode($product, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}') }" @click="$dispatch('add-to-cart', { product: product })"
                            class="bg-white rounded-lg shadow-sm hover:shadow-md active:scale-95 transition-all duration-150 cursor-pointer border border-gray-900 ">
                            <div class="p-2 flex flex-col h-full">
                                <!-- Product Name -->
                                <div class="mb-1">
                                    <h3 class="text-sm font-semibold text-gray-800 leading-tight line-clamp-2">
                                        {{ $product->name }}
                                    </h3>
                                    <div class="text-xs text-gray-500">{{ $product->code }}</div>
                                </div>

                                <!-- Price and Stock -->
                                <div class="mt-auto">
                                    <div class="flex items-center justify-between">
                                        <div class="text-base font-bold text-blue-600">
                                            {{ number_format($product->retail_price, 0, ',', '.') }}
                                        </div>
                                        <div
                                            class="px-1.5 py-0.5 text-xs font-medium rounded-lg
                                                {{ $product->stock > 5
                                                    ? 'bg-green-100 text-green-700'
                                                    : ($product->stock > 0
                                                        ? 'bg-yellow-100 text-yellow-700'
                                                        : 'bg-red-100 text-red-700') }}">
                                            Stok: {{ $product->stock }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            </div>
        </div>

        <!-- Cart Section -->
      <div
    class="w-full md:w-1/3 bg-white flex flex-col border  rounded-2xl shadow-2xl border-gray-700 overflow-hidden mx-auto md:mx-3 mt-4 md:mt-0 z-50 ">
            <!-- Cart Header -->
            <div class="p-3 bg-white border-b border-gray-200 flex items-center justify-between rounded-t-lg">
                <div>
                    <h2 class="font-bold text-lg text-gray-800">Keranjang</h2>

                </div>
                <button wire:click="$dispatch('open-customer-modal')" class="flex items-center gap-2 px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-sm font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>Pilih Pelanggan</span>
                </button>
            </div>

            <!-- Cart Content -->
            <div class="flex-1 overflow-hidden border-spacing-1 bg-slate-500">
                <livewire:pos.cart />
            </div>
        </div>
    </div>

    <!-- Floating Notification -->
    <div x-data="{ show: false, message: '', type: 'success' }"
        x-on:show-alert.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => { show = false }, 3000)"
        x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        class="fixed bottom-safe inset-x-0 mx-auto max-w-sm px-4 py-3 rounded-t-xl text-white text-center font-medium shadow-lg"
        :class="{ 'bg-green-500': type === 'success', 'bg-red-500': type === 'error' }" style="display: none;">
        <p x-text="message"></p>
    </div>
</div>
