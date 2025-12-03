<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Filter Inventory</h2>
                <button wire:click="exportInventoryCsv" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export CSV
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="relative">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" wire:model.live="search" placeholder="Search product..."
                            class="pl-10 block w-full rounded-lg border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition duration-150 ease-in-out">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Category</label>
                    <select wire:model.live="categoryFilter" class="block w-full rounded-lg border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition duration-150 ease-in-out">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Stock Status</label>
                    <select wire:model.live="stockFilter" class="block w-full rounded-lg border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition duration-150 ease-in-out">
                        <option value="all">All Items</option>
                        <option value="low">Low Stock (<=10)</option>
                        <option value="out">Out of Stock (0)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Store Unit</label>
                    <select wire:model.live="storeFilter" class="block w-full rounded-lg border-gray-200 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition duration-150 ease-in-out">
                        <option value="">All Stores</option>
                        <option value="giling_bakso">Giling Bakso</option>
                        <option value="nanang_store">Nanang Store</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Value Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center space-x-4">
                <div class="p-3 rounded-full bg-blue-50 text-blue-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Inventory Value</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalValue, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Low Stock Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center space-x-4">
                <div class="p-3 rounded-full bg-yellow-50 text-yellow-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Low Stock Items</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $lowStockCount }}</p>
                </div>
            </div>

            <!-- Out of Stock Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center space-x-4">
                <div class="p-3 rounded-full bg-red-50 text-red-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Out of Stock Items</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $outOfStockCount }}</p>
                </div>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 ">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('code')">
                            Code
                            @if($sortField === 'code')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('name')">
                            Name
                            @if($sortField === 'name')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('stock')">
                            Stock
                            @if($sortField === 'stock')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('retail_price')">
                            Retail Price
                            @if($sortField === 'retail_price')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('wholesale_price')">
                            Wholesale Price
                            @if($sortField === 'wholesale_price')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            History
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($inventoryData as $product)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $product->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ $product->category->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->stock == 0)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Out of Stock
                                    </span>
                                @elseif($product->stock <= 10)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Low: {{ number_format($product->stock, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ number_format($product->stock, 0, ',', '.') }}
                                    </span>
                                @endif
                                
                                @if($product->units_in_box > 1)
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $product->box_stock }} {{ $product->boxUnit->code ?? 'Box' }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp {{ number_format($product->retail_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp {{ number_format($product->wholesale_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button wire:click="openHistoryModal({{ $product->id }})" class="text-blue-600 hover:text-blue-900" title="View History">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="openAdjustmentModal({{ $product->id }})" class="text-green-600 hover:text-green-900" title="Adjust Stock">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-6 py-4 bg-gray-50">
                {{ $inventoryData->links() }}
            </div>
        </div>
    </div>

    <!-- History Modal -->
    @if($showHistoryModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeHistoryModal"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <div class="flex justify-between items-center border-b pb-3 mb-4">
                                <h3 class="text-lg leading-6 font-semibold text-gray-900" id="modal-title">
                                    Stock Card: <span class="text-blue-600">{{ $selectedProduct->name }}</span>
                                </h3>
                                <span class="px-2 py-1 text-xs font-semibold rounded-md bg-gray-100 text-gray-600">{{ $selectedProduct->code }}</span>
                            </div>
                            
                            <div class="flex justify-between items-center mb-4 bg-gray-50 p-3 rounded-lg">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Current Stock</p>
                                    <p class="text-xl font-bold text-gray-900">{{ $selectedProduct->stock }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Period</p>
                                    <p class="text-sm font-medium text-gray-900">Last 30 Days</p>
                                </div>
                            </div>
                            
                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">In</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Out</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($movementData as $movement)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                                {{ \Carbon\Carbon::parse($movement['date'])->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                                @if($movement['type'] == 'sale')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Sale</span>
                                                @elseif($movement['type'] == 'purchase')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Purchase</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Adj</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono">
                                                {{ $movement['reference'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-green-600">
                                                {{ $movement['quantity_in'] > 0 ? '+' . number_format($movement['quantity_in'], 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-red-600">
                                                {{ $movement['quantity_out'] > 0 ? '-' . number_format($movement['quantity_out'], 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 text-xs text-gray-500">
                                                {{ $movement['notes'] }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                                </svg>
                                                <p class="mt-2">No movement history found for the last 30 days.</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="closeHistoryModal" class="w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Adjustment Modal -->
    @if($showAdjustmentModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeAdjustmentModal"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-semibold text-gray-900 mb-4" id="modal-title">
                                Adjust Stock: <span class="text-blue-600">{{ $selectedProduct->name }}</span>
                            </h3>
                            <div class="mt-4 space-y-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Adjustment Type</label>
                                    <select wire:model="adjustmentType" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <option value="repack_out">Repack Out (Kurangi Stok)</option>
                                        <option value="repack_in">Repack In (Tambah Stok)</option>
                                        <option value="damage">Barang Rusak/Expired (Kurangi Stok)</option>
                                        <option value="internal_use">Pemakaian Sendiri (Kurangi Stok)</option>
                                        <option value="correction_in">Koreksi Masuk (Tambah Stok)</option>
                                        <option value="correction_out">Koreksi Keluar (Kurangi Stok)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity (Positif/Negatif)</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <input type="number" wire:model="adjustmentQuantity" class="block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="0">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Gunakan angka negatif (-) untuk mengurangi stok, positif (+) untuk menambah.</p>
                                    @error('adjustmentQuantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                    <textarea wire:model="adjustmentNotes" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Reason for adjustment..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="adjustStock" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Save Adjustment
                    </button>
                    <button type="button" wire:click="closeAdjustmentModal" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
