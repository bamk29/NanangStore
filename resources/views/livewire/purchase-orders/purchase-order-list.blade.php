<div>
    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-bold text-gray-900">Purchase Orders</h1>
                <p class="mt-2 text-sm text-gray-700">Daftar semua pesanan barang ke supplier.</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <a href="{{ route('purchase-orders.create') }}" class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                    Buat PO Baru
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="mt-6 p-4 bg-white rounded-lg shadow">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Cari No. PO</label>
                    <input wire:model.live.debounce.300ms="search" type="text" id="search" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="supplierFilter" class="block text-sm font-medium text-gray-700">Filter Supplier</label>
                    <select wire:model.live="supplierFilter" id="supplierFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Semua Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="statusFilter" class="block text-sm font-medium text-gray-700">Filter Status</label>
                    <select wire:model.live="statusFilter" id="statusFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="ordered">Ordered</option>
                        <option value="received">Received</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Mobile View -->
        <div class="mt-6 space-y-4 sm:hidden">
            @forelse ($purchaseOrders as $po)
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-blue-600">{{ $po->order_number }}</p>
                            <p class="text-sm text-gray-600">{{ $po->supplier->name }}</p>
                        </div>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            @switch($po->status)
                                @case('draft') bg-gray-100 text-gray-800 @break
                                @case('ordered') bg-blue-100 text-blue-800 @break
                                @case('received') bg-green-100 text-green-800 @break
                                @case('cancelled') bg-red-100 text-red-800 @break
                            @endswitch
                        ">
                            {{ ucfirst($po->status) }}
                        </span>
                    </div>
                    <div class="mt-4 flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($po->order_date)->format('d M Y') }}</p>
                            <p class="font-bold text-gray-800">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('purchase-orders.edit', $po->id) }}" class="p-2 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200" title="Lihat/Edit">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </a>
                            @if($po->status === 'draft')
                                <button wire:click="confirmDelete({{ $po->id }})" class="p-2 rounded-full bg-red-100 text-red-600 hover:bg-red-200" title="Hapus">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow rounded-lg p-6 text-center">
                    <p class="text-gray-500">Tidak ada Purchase Order ditemukan.</p>
                </div>
            @endforelse
        </div>

        <!-- Desktop Table -->
        <div class="-mx-4 mt-8 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:-mx-6 md:mx-0 md:rounded-lg hidden sm:block">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Nomor PO</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Supplier</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tgl Order</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="relative py-3.5 pl-3 pr-4 sm:pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($purchaseOrders as $po)
                        <tr>
                            <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $po->order_number }}</td>
                            <td class="px-3 py-4 text-sm text-gray-500">{{ $po->supplier->name }}</td>
                            <td class="px-3 py-4 text-sm text-gray-500">{{ \Carbon\Carbon::parse($po->order_date)->format('d M Y') }}</td>
                            <td class="px-3 py-4 text-sm text-gray-500">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                            <td class="px-3 py-4 text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @switch($po->status)
                                        @case('draft') bg-gray-100 text-gray-800 @break
                                        @case('ordered') bg-blue-100 text-blue-800 @break
                                        @case('received') bg-green-100 text-green-800 @break
                                        @case('cancelled') bg-red-100 text-red-800 @break
                                    @endswitch
                                ">
                                    {{ ucfirst($po->status) }}
                                </span>
                            </td>
                            <td class="py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('purchase-orders.edit', $po->id) }}" class="p-2 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200" title="Lihat/Edit">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    @if($po->status === 'ordered' || $po->status === 'partially_received')
                                    <button wire:click="receiveOrder({{ $po->id }})" wire:confirm="Anda yakin ingin menerima semua barang di PO ini? Stok akan ditambahkan." class="p-2 rounded-full bg-green-100 text-green-600 hover:bg-green-200" title="Terima Barang">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    </button>
                                    @endif
                                    @if(in_array($po->status, ['draft', 'ordered']))
                                    <button wire:click="cancelOrder({{ $po->id }})" wire:confirm="Anda yakin ingin membatalkan PO ini?" class="p-2 rounded-full bg-yellow-100 text-yellow-600 hover:bg-yellow-200" title="Batal">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                    @endif
                                    @if($po->status === 'draft')
                                    <button wire:click="confirmDelete({{ $po->id }})" class="p-2 rounded-full bg-red-100 text-red-600 hover:bg-red-200" title="Hapus">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada Purchase Order ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">
                {{ $purchaseOrders->links() }}
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($poToDeleteId)
    <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-bold mb-2">Hapus Purchase Order?</h3>
            <p class="text-sm text-gray-600 mb-4">Anda yakin ingin menghapus PO ini secara permanen? Aksi ini tidak dapat diurungkan.</p>
            <div class="flex justify-end space-x-4">
                <button wire:click="$set('poToDeleteId', null)" class="px-4 py-2 rounded-lg text-gray-600 bg-gray-100 hover:bg-gray-200">Tidak</button>
                <button wire:click="delete()" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Ya, Hapus Permanen</button>
            </div>
        </div>
    </div>
    @endif
</div>
