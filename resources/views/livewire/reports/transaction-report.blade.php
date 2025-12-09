<div>
    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Header and Filters -->
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-bold text-gray-900">Laporan Transaksi</h1>
                <p class="mt-2 text-sm text-gray-700">Analisis semua transaksi berdasarkan rentang waktu, status, dan toko.</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <button wire:click="exportExcel" type="button" class="inline-flex items-center justify-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 sm:w-auto">
                    Export ke Excel
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="mt-6 p-4 bg-white rounded-lg shadow">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                    <input wire:model="startDate" type="date" id="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                    <input wire:model="endDate" type="date" id="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select wire:model="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="all">Semua</option>
                        <option value="completed">Selesai</option>
                        <option value="pending">Tertunda</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label for="product" class="block text-sm font-medium text-gray-700">Produk</label>
                    <select wire:model="selectedProductId" id="product" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="all">Semua Produk</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Filter Toko</label>
                    <div class="mt-1 isolate inline-flex rounded-md shadow-sm w-full">
                        <button wire:click="$set('storeFilter', 'all')" type="button" class="relative inline-flex items-center justify-center rounded-l-md border px-3 py-2 text-sm font-medium w-1/3 {{ $storeFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">Semua</button>
                        <button wire:click="$set('storeFilter', 'bakso')" type="button" class="relative -ml-px inline-flex items-center justify-center border px-3 py-2 text-sm font-medium w-1/3 {{ $storeFilter === 'bakso' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">Bakso</button>
                        <button wire:click="$set('storeFilter', 'nanang_store')" type="button" class="relative -ml-px inline-flex items-center justify-center rounded-r-md border px-3 py-2 text-sm font-medium w-1/3 {{ $storeFilter === 'nanang_store' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">Toko</button>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button wire:click="applyFilters" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Terapkan Filter
                </button>
            </div>
        </div>

        <!-- Column Visibility Filter -->
        <div class="mt-4 flex justify-end">
            <div x-data="{ open: false }" class="relative inline-block text-left">
                <div>
                    <button @click="open = !open" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="menu-button" aria-expanded="true" aria-haspopup="true">
                        Filter Kolom
                        <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                    <div class="py-1" role="none">
                        @foreach($visibleColumns as $column => $isVisible)
                            <label class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer">
                                <input type="checkbox" wire:model.live="visibleColumns.{{ $column }}" class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                <span class="ml-2">{{ ucfirst(str_replace('_', ' ', $column)) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Table -->
        <div class="mt-4 flex flex-col" x-data="{
            // Column filters
            invoiceFilter: '',
            selectedCustomers: [],
            minQty: null,
            maxQty: null,
            minTotal: null,
            maxTotal: null,
            selectedPaymentMethods: [],
            selectedStatuses: [],
            selectedItems: [],
            availableItems: [],
            
            init() {
                this.$watch('invoiceFilter', () => this.filteredTransactions); 
                this.$watch('selectedCustomers', () => this.filteredTransactions); 
                this.$watch('minQty', () => this.filteredTransactions); 
                this.$watch('maxQty', () => this.filteredTransactions); 
                this.$watch('minTotal', () => this.filteredTransactions); 
                this.$watch('maxTotal', () => this.filteredTransactions); 
                this.$watch('selectedPaymentMethods', () => this.filteredTransactions); 
                this.$watch('selectedStatuses', () => this.filteredTransactions);
                this.$watch('selectedItems', () => this.filteredTransactions);
                
                // Initialize items list logic
                this.$nextTick(() => {
                    this.scanItems();
                });
            },

            scanItems() {
                let items = new Set();
                if (this.$refs.transactionRows) {
                    Array.from(this.$refs.transactionRows.children).forEach(row => {
                        const rowItems = row.dataset.items ? row.dataset.items.split(',') : [];
                        rowItems.forEach(item => {
                            if(item) items.add(item.trim());
                        });
                    });
                }
                this.availableItems = Array.from(items).sort();
            },
            
            // Get filtered transactions
            get filteredTransactions() {
                let filtered = $refs.transactionRows ? Array.from($refs.transactionRows.children) : [];
                
                filtered.forEach(row => {
                    let show = true;
                    
                    // Invoice filter
                    if (this.invoiceFilter) {
                        const invoice = row.dataset.invoice || '';
                        show = show && invoice.toLowerCase().includes(this.invoiceFilter.toLowerCase());
                    }
                    
                    // Customer filter
                    if (this.selectedCustomers.length > 0) {
                        const customerId = row.dataset.customerId;
                        show = show && this.selectedCustomers.includes(customerId);
                    }
                    
                    // Quantity filter
                    if (this.minQty !== null && this.minQty !== '') {
                        const qty = parseFloat(row.dataset.qty) || 0;
                        show = show && qty >= parseFloat(this.minQty);
                    }
                    if (this.maxQty !== null && this.maxQty !== '') {
                        const qty = parseFloat(row.dataset.qty) || 0;
                        show = show && qty <= parseFloat(this.maxQty);
                    }
                    
                    // Total filter
                    if (this.minTotal !== null && this.minTotal !== '') {
                        const total = parseFloat(row.dataset.total) || 0;
                        show = show && total >= parseFloat(this.minTotal);
                    }
                    if (this.maxTotal !== null && this.maxTotal !== '') {
                        const total = parseFloat(row.dataset.total) || 0;
                        show = show && total <= parseFloat(this.maxTotal);
                    }
                    
                    // Payment method filter
                    if (this.selectedPaymentMethods.length > 0) {
                        const payment = row.dataset.payment;
                        show = show && this.selectedPaymentMethods.includes(payment);
                    }
                    
                    // Status filter
                    if (this.selectedStatuses.length > 0) {
                        const status = row.dataset.status;
                        show = show && this.selectedStatuses.includes(status);
                    }

                    // Item filter
                    if (this.selectedItems.length > 0) {
                        const rowItems = row.dataset.items ? row.dataset.items.toLowerCase() : '';
                        // Show if ANY selected item is present in the row
                        const hasItem = this.selectedItems.some(item => rowItems.includes(item.toLowerCase()));
                        show = show && hasItem;
                    }
                    
                    row.style.display = show ? '' : 'none';
                });
                
                return filtered.filter(row => row.style.display !== 'none').length;
            },
            
            clearFilters() {
                this.invoiceFilter = '';
                this.selectedCustomers = [];
                this.minQty = null;
                this.maxQty = null;
                this.minTotal = null;
                this.maxTotal = null;
                this.selectedPaymentMethods = [];
                this.selectedStatuses = [];
                this.selectedItems = [];
                this.filteredTransactions; // Trigger filter
            }
        }">
            <div class="flex justify-between items-center mb-2">
                <div class="text-sm text-gray-600">
                    <span x-text="filteredTransactions"></span> dari {{ $transactions->count() }} transaksi ditampilkan
                </div>
                <button @click="clearFilters()" type="button" class="text-xs text-indigo-600 hover:text-indigo-800">
                    Reset Filter Kolom
                </button>
            </div>
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    @if($visibleColumns['number'])
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">No</th>
                                    @endif
                                    @if($visibleColumns['invoice_number'])
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                            <div class="flex items-center gap-2">
                                                <span class="cursor-pointer hover:bg-gray-100" wire:click="sortBy('invoice_number')">
                                                    Invoice
                                                    @if ($sortColumn === 'invoice_number')
                                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                    @endif
                                                </span>
                                                <!-- Filter Invoice -->
                                                <div x-data="{ open: false }" class="relative">
                                                    <button @click.stop="open = !open" class="text-gray-400 hover:text-gray-600">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                                        </svg>
                                                    </button>
                                                    <div x-show="open" @click.away="open = false" class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg z-20 p-2 border">
                                                        <input type="text" x-model="invoiceFilter" placeholder="Cari Invoice..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs">
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                    @endif
                                    @if($visibleColumns['created_at'])
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 cursor-pointer hover:bg-gray-100" wire:click="sortBy('created_at')">
                                            Waktu
                                            @if ($sortColumn === 'created_at')
                                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </th>
                                    @endif
                                    @if($visibleColumns['customer_id'])
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                            <div class="flex items-center gap-2">
                                                <span class="cursor-pointer hover:bg-gray-100" wire:click="sortBy('customer_id')">
                                                    Pelanggan
                                                    @if ($sortColumn === 'customer_id')
                                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                    @endif
                                                </span>
                                                <!-- Filter Customer -->
                                                <div x-data="{ open: false }" class="relative">
                                                    <button @click.stop="open = !open" class="text-gray-400 hover:text-gray-600">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                                        </svg>
                                                    </button>
                                                    <div x-show="open" @click.away="open = false" class="absolute left-0 mt-2 w-64 bg-white rounded-md shadow-lg z-20 p-2 border max-h-60 overflow-y-auto">
                                                        @forelse($availableCustomers as $customer)
                                                            <label class="flex items-center px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer">
                                                                <input type="checkbox" x-model="selectedCustomers" value="{{ $customer->id }}" class="form-checkbox h-3 w-3 text-indigo-600 transition duration-150 ease-in-out">
                                                                <span class="ml-2">{{ $customer->name }}</span>
                                                            </label>
                                                        @empty
                                                            <div class="px-2 py-1 text-xs text-gray-500">Tidak ada pelanggan</div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                    @endif
                                    @if($visibleColumns['items'])
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                            <div class="flex items-center gap-2">
                                                <span>Item (Qty)</span>
                                                <!-- Filter Item and Qty -->
                                                <div x-data="{ open: false }" class="relative">
                                                    <button @click.stop="open = !open" class="text-gray-400 hover:text-gray-600">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                                        </svg>
                                                    </button>
                                                    <div x-show="open" @click.away="open = false" class="absolute left-0 mt-2 w-64 bg-white rounded-md shadow-lg z-20 p-2 border">
                                                        <!-- Item Filter List -->
                                                        <div class="mb-2 border-b pb-2">
                                                            <div class="text-xs font-semibold text-gray-500 mb-1">Filter Barang</div>
                                                            <div class="max-h-40 overflow-y-auto space-y-1">
                                                                <template x-for="item in availableItems" :key="item">
                                                                    <label class="flex items-center px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer">
                                                                        <input type="checkbox" x-model="selectedItems" :value="item" class="form-checkbox h-3 w-3 text-indigo-600 transition duration-150 ease-in-out">
                                                                        <span class="ml-2" x-text="item"></span>
                                                                    </label>
                                                                </template>
                                                                <div x-show="availableItems.length === 0" class="px-2 py-1 text-xs text-gray-500">Tidak ada item</div>
                                                            </div>
                                                        </div>

                                                        <div class="space-y-2">
                                                            <div class="text-xs font-semibold text-gray-500">Filter Qty</div>
                                                            <input type="number" x-model="minQty" placeholder="Min Qty" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs">
                                                            <input type="number" x-model="maxQty" placeholder="Max Qty" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                    @endif

                                    @if($visibleColumns['total_amount'])
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 cursor-pointer hover:bg-gray-100" wire:click="sortBy('total_amount')">
                                            <div class="flex items-center gap-2">
                                                <span class="cursor-pointer hover:bg-gray-100" wire:click="sortBy('total_amount')">
                                                    Total
                                                    @if ($sortColumn === 'total_amount')
                                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                    @endif
                                                </span>
                                                <!-- Filter Total -->
                                                <div x-data="{ open: false }" class="relative">
                                                    <button @click.stop="open = !open" class="text-gray-400 hover:text-gray-600">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                                        </svg>
                                                    </button>
                                                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-20 p-2 border">
                                                        <div class="space-y-2">
                                                            <input type="number" x-model="minTotal" placeholder="Min Rp" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs">
                                                            <input type="number" x-model="maxTotal" placeholder="Max Rp" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                    @endif
                                    @if($visibleColumns['payment_method'])
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                            <div class="flex items-center gap-2">
                                                <span class="cursor-pointer hover:bg-gray-100" wire:click="sortBy('payment_method')">
                                                    Metode
                                                    @if ($sortColumn === 'payment_method')
                                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                    @endif
                                                </span>
                                                <!-- Filter Payment -->
                                                <div x-data="{ open: false }" class="relative">
                                                    <button @click.stop="open = !open" class="text-gray-400 hover:text-gray-600">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                                        </svg>
                                                    </button>
                                                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg z-20 p-2 border">
                                                        <label class="flex items-center px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer">
                                                            <input type="checkbox" x-model="selectedPaymentMethods" value="cash" class="form-checkbox h-3 w-3 text-indigo-600 transition duration-150 ease-in-out">
                                                            <span class="ml-2">Cash</span>
                                                        </label>
                                                        <label class="flex items-center px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer">
                                                            <input type="checkbox" x-model="selectedPaymentMethods" value="transfer" class="form-checkbox h-3 w-3 text-indigo-600 transition duration-150 ease-in-out">
                                                            <span class="ml-2">Transfer</span>
                                                        </label>
                                                        <label class="flex items-center px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer">
                                                            <input type="checkbox" x-model="selectedPaymentMethods" value="debt" class="form-checkbox h-3 w-3 text-indigo-600 transition duration-150 ease-in-out">
                                                            <span class="ml-2">Debt</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                    @endif
                                    @if($visibleColumns['status'])
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                            <div class="flex items-center gap-2">
                                                <span class="cursor-pointer hover:bg-gray-100" wire:click="sortBy('status')">
                                                    Status
                                                    @if ($sortColumn === 'status')
                                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                    @endif
                                                </span>
                                                <!-- Filter Status -->
                                                <div x-data="{ open: false }" class="relative">
                                                    <button @click.stop="open = !open" class="text-gray-400 hover:text-gray-600">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                                        </svg>
                                                    </button>
                                                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg z-20 p-2 border">
                                                        <label class="flex items-center px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer">
                                                            <input type="checkbox" x-model="selectedStatuses" value="completed" class="form-checkbox h-3 w-3 text-indigo-600 transition duration-150 ease-in-out">
                                                            <span class="ml-2">Selesai</span>
                                                        </label>
                                                        <label class="flex items-center px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer">
                                                            <input type="checkbox" x-model="selectedStatuses" value="pending" class="form-checkbox h-3 w-3 text-indigo-600 transition duration-150 ease-in-out">
                                                            <span class="ml-2">Tertunda</span>
                                                        </label>
                                                        <label class="flex items-center px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer">
                                                            <input type="checkbox" x-model="selectedStatuses" value="cancelled" class="form-checkbox h-3 w-3 text-indigo-600 transition duration-150 ease-in-out">
                                                            <span class="ml-2">Dibatalkan</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white" x-ref="transactionRows">
                                @forelse ($transactions as $transaction)
                                    @php
                                        $totalQty = $transaction->details->filter(function($d) use ($selectedProductId) {
                                            return $selectedProductId === 'all' || $d->product_id == $selectedProductId;
                                        })->sum('quantity');

                                        $itemNames = $transaction->details->map(function($detail) {
                                            return $detail->product->name ?? 'Produk Dihapus';
                                        })->implode(',');
                                    @endphp
                                    <tr 
                                        data-invoice="{{ $transaction->invoice_number }}"
                                        data-customer-id="{{ $transaction->customer_id }}"
                                        data-qty="{{ $totalQty }}"
                                        data-total="{{ $transaction->total_amount }}"
                                        data-payment="{{ $transaction->payment_method }}"
                                        data-status="{{ $transaction->status }}"
                                        data-items="{{ $itemNames }}"
                                    >
                                        @if($visibleColumns['number'])
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                {{ $loop->iteration }}
                                            </td>
                                        @endif
                                        @if($visibleColumns['invoice_number'])
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                {{ $transaction->invoice_number }}
                                            </td>
                                        @endif
                                        @if($visibleColumns['created_at'])
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{ $transaction->created_at->format('d/m/Y H:i') }}
                                            </td>
                                        @endif
                                        @if($visibleColumns['customer_id'])
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{ $transaction->customer->name ?? '-' }}
                                            </td>
                                        @endif
                                        @if($visibleColumns['items'])
                                            <td class="px-3 py-4 text-sm text-gray-500">
                                                <ul class="list-disc list-inside">
                                                    @foreach ($transaction->details as $detail)
                                                        @if($selectedProductId === 'all' || $detail->product_id == $selectedProductId)
                                                            <li>
                                                                {{ $detail->product->name ?? 'Produk Dihapus' }} 
                                                                <span class="text-gray-600 font-medium">x {{ number_format($detail->quantity, 0, ',', '.') }}</span>
                                                                <span class="text-gray-500 text-xs">(Rp {{ number_format($detail->subtotal, 0, ',', '.') }})</span>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </td>
                                        @endif

                                        @if($visibleColumns['total_amount'])
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 font-medium">
                                                Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                                            </td>
                                        @endif
                                        @if($visibleColumns['payment_method'])
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $transaction->payment_method == 'debt' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                                    {{ ucfirst($transaction->payment_method) }}
                                                </span>
                                            </td>
                                        @endif
                                        @if($visibleColumns['status'])
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 
                                                    @switch($transaction->status)
                                                        @case('completed') bg-green-100 text-green-800 @break
                                                        @case('pending') bg-yellow-100 text-yellow-800 @break
                                                        @case('cancelled') bg-red-100 text-red-800 @break
                                                        @default bg-gray-100 text-gray-800
                                                    @endswitch">
                                                    {{ ucfirst($transaction->status) }}
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count(array_filter($visibleColumns)) }}" class="px-3 py-4 text-sm text-gray-500 text-center">
                                            Tidak ada transaksi yang cocok dengan filter yang dipilih.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    @php
                                        $colspan = 0;
                                        if($visibleColumns['number']) $colspan++;
                                        if($visibleColumns['invoice_number']) $colspan++;
                                        if($visibleColumns['created_at']) $colspan++;
                                        if($visibleColumns['customer_id']) $colspan++;
                                    @endphp
                                    <td colspan="{{ $colspan }}" class="py-3.5 pl-4 pr-3 text-right text-sm font-semibold text-gray-900 sm:pl-6">
                                        Total ({{ $transactions->count() }} Transaksi)
                                    </td>
                                    @if($visibleColumns['items'])
                                        <td class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                            @php
                                                $totalQty = $transactions->sum(function($t) use ($selectedProductId) {
                                                    return $t->details->filter(function($d) use ($selectedProductId) {
                                                         return $selectedProductId === 'all' || $d->product_id == $selectedProductId;
                                                    })->sum('quantity');
                                                });
                                            @endphp
                                            Total Qty: {{ number_format($totalQty, 0, ',', '.') }}
                                        </td>
                                    @endif

                                    @if($visibleColumns['total_amount'])
                                        <td class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                            Rp {{ number_format($transactions->sum('total_amount'), 0, ',', '.') }}
                                        </td>
                                    @endif
                                    @if($visibleColumns['payment_method']) <td></td> @endif
                                    @if($visibleColumns['status']) <td></td> @endif
                                </tr>
                            </tfoot>
    </div>
</div>
