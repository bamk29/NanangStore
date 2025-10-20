<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" wire:model.live="search" placeholder="Search by name or code..."
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <select wire:model.live="categoryFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Stock Status</label>
                    <select wire:model.live="stockFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="all">All Items</option>
                        <option value="low">Low Stock</option>
                        <option value="out">Out of Stock</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button wire:click="exportInventoryCsv" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                    Export CSV
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900">Total Inventory Value</h3>
                <p class="mt-2 text-3xl font-bold text-blue-600">Rp {{ number_format($totalValue, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900">Low Stock Items</h3>
                <p class="mt-2 text-3xl font-bold text-yellow-600">{{ $lowStockCount }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900">Out of Stock Items</h3>
                <p class="mt-2 text-3xl font-bold text-red-600">{{ $outOfStockCount }}</p>
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
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($inventoryData as $product)
                        <tr class="{{ $product->stock <= 10 ? ($product->stock === 0 ? 'bg-red-50' : 'bg-yellow-50') : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->category->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $product->stock <= 10 ? ($product->stock === 0 ? 'text-red-600 font-semibold' : 'text-yellow-600 font-semibold') : 'text-gray-900' }}">
                                @if($product->units_in_box > 1)
                                    {{ $product->box_stock }} {{ $product->boxUnit->code ?? 'Box' }} / {{ number_format($product->stock, 0, ',', '.') }} {{ $product->baseUnit->code ?? 'Pcs' }}
                                @else
                                    {{ number_format($product->stock, 0, ',', '.') }} {{ $product->baseUnit->code ?? 'Pcs' }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($product->retail_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($product->wholesale_price, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-6 py-4 bg-gray-50">
                {{ $inventoryData->links() }}
            </div>
        </div>
    </div>
</div>
