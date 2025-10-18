<div x-data="quantityModal()" class="p-3 md:p-6 bg-gray-50 min-h-screen">
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
                        <div x-data="{ product: JSON.parse('{{ json_encode($product, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}') }" @click="openModal(product)"
                            class="bg-white rounded-lg shadow-sm hover:shadow-md active:scale-95 transition-all duration-150 cursor-pointer border ">
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
    class="w-full md:w-1/3 bg-white flex flex-col border  rounded-2xl shadow-2xl border-gray-200 overflow-hidden mx-auto md:mx-3 mt-4 md:mt-0 z-50 ">
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
            <div class="flex-1 overflow-hidden border-spacing-1 bg-gray-100">
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

    <!-- Quantity Modal -->
    <div x-show="isModalOpen" x-cloak class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div @click.away="closeModal()" class="bg-white rounded-lg shadow-xl p-5 md:p-6 w-full max-w-md mx-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900" x-text="productForModal ? productForModal.name : ''"></h3>
                <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <div class="mb-6">
                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Kuantitas</label>
                <div class="flex items-center rounded-md shadow-sm">
                    <button type="button" @click="decrement()"  class="w-8 h-8 flex items-center justify-center mx-2 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 active:scale-95 transition-all duration-150 focus:outline-none">-</button>
                    <input type="number" step="0.01" id="quantity" name="quantity" x-ref="quantityInput" x-model="quantity" @input="validate()" @keydown.enter.prevent="if(isQuantityValid) addToCartFromModal()" class="block w-full text-center border-gray-300 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <button type="button" @click="increment()"  class="w-8 h-8 flex items-center justify-center mx-2 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 active:scale-95 transition-all duration-150 focus:outline-none">+</button>
                </div>
                <p class="text-xs text-gray-500 mt-2" x-text="productForModal ? 'Stok tersedia: ' + productForModal.stock : ''"></p>
                <p x-show="!isQuantityValid" class="text-sm text-red-600 mt-2" x-text="errorMessage"></p>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">Batal</button>
                <button type="button" @click="addToCartFromModal()" :disabled="!isQuantityValid" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">Masukan ke Keranjang</button>
            </div>
        </div>
    </div>
</div>

<script>
    function quantityModal() {
        return {
            isModalOpen: false,
            productForModal: null,
            quantity: 1,
            isQuantityValid: true,
            errorMessage: '',

            openModal(product) {
                if (product.stock <= 0) {
                    window.Livewire.dispatch('show-alert', { type: 'error', message: 'Stok produk habis.' });
                    return;
                }
                this.productForModal = product;
                this.quantity = 1;
                this.isQuantityValid = true;
                this.errorMessage = '';
                this.isModalOpen = true;
                this.$nextTick(() => {
                    this.$refs.quantityInput.focus();
                    this.$refs.quantityInput.select();
                });
            },

            closeModal() {
                this.isModalOpen = false;
                this.productForModal = null;
            },

            increment() {
                let currentQuantity = parseFloat(this.quantity) || 0;
                this.quantity = currentQuantity + 1;
                this.validate();
            },

            decrement() {
                let currentQuantity = parseFloat(this.quantity) || 0;
                if (currentQuantity > 1) {
                    this.quantity = currentQuantity - 1;
                }
                this.validate();
            },

            validate() {
                let qty = parseFloat(this.quantity);
                if (isNaN(qty) || qty <= 0) {
                    this.isQuantityValid = false;
                    this.errorMessage = 'Kuantitas harus lebih dari 0.';
                    return;
                }
                if (qty > this.productForModal.stock) {
                    this.isQuantityValid = false;
                    this.errorMessage = 'Kuantitas melebihi stok yang tersedia.';
                    return;
                }
                this.isQuantityValid = true;
                this.errorMessage = '';
            },

            addToCartFromModal() {
                this.validate();
                if (!this.isQuantityValid) return;

                this.$dispatch('add-to-cart', {
                    product: this.productForModal,
                    quantity: this.quantity
                });
                this.closeModal();
            }
        }
    }
</script>
