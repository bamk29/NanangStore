<div>
    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Header and Filters -->
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-bold text-gray-900">Rincian Transaksi Harian</h1>
                <p class="mt-2 text-sm text-gray-700">Lihat semua item yang terjual dalam setiap transaksi per hari.</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="mt-6 p-4 bg-white rounded-lg shadow">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="selectedDate" class="block text-sm font-medium text-gray-700">Pilih Tanggal</label>
                    <input wire:model.live="selectedDate" type="date" id="selectedDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Filter Toko</label>
                    <div class="mt-1 isolate inline-flex rounded-md shadow-sm w-full">
                        <button wire:click="setStoreFilter('all')" type="button" class="relative inline-flex items-center justify-center rounded-l-md border px-3 py-2 text-sm font-medium w-1/3 {{ $storeFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">Semua</button>
                        <button wire:click="setStoreFilter('bakso')" type="button" class="relative -ml-px inline-flex items-center justify-center border px-3 py-2 text-sm font-medium w-1/3 {{ $storeFilter === 'bakso' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">Bakso</button>
                        <button wire:click="setStoreFilter('nanang_store')" type="button" class="relative -ml-px inline-flex items-center justify-center rounded-r-md border px-3 py-2 text-sm font-medium w-1/3 {{ $storeFilter === 'nanang_store' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">Toko</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction List -->
        <div class="mt-8 space-y-6">
            @forelse ($transactions as $transaction)
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <!-- Transaction Header -->
                    <div class="p-4 sm:p-6 bg-gray-50 border-b border-gray-200 grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Invoice</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $transaction->invoice_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Waktu</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $transaction->created_at->format('H:i:s') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Pelanggan</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $transaction->customer->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Total</dt>
                            <dd class="mt-1 text-sm font-bold text-blue-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</dd>
                        </div>
                    </div>
                    <!-- Transaction Details (Products) -->
                    <div class="p-4 sm:p-6">
                        <h4 class="text-xs font-medium text-gray-500 uppercase mb-2">Item Terjual</h4>
                        <ul role="list" class="divide-y divide-gray-200">
                            @foreach ($transaction->details as $detail)
                                <li class="py-3 flex justify-between items-center">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $detail->product->name ?? 'Produk Dihapus' }}</p>
                                        <p class="text-sm text-gray-500">{{ number_format($detail->quantity) }} x Rp {{ number_format($detail->price, 0, ',', '.') }}</p>
                                    </div>
                                    <p class="text-sm font-medium text-gray-800">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</p>
                                </li>
                            @endforeach
                        </ul>

                        <!-- Payment Details Section -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <h4 class="text-xs font-medium text-gray-500 uppercase mb-2">Detail Pembayaran</h4>
                            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                <div class="col-span-1">
                                    <dt class="text-gray-500">Metode Bayar</dt>
                                    <dd class="font-medium text-gray-900">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $transaction->payment_method == 'debt' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ ucfirst($transaction->payment_method) }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="col-span-1">
                                    <dt class="text-gray-500">Status</dt>
                                    <dd class="font-medium text-gray-900">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @switch($transaction->status)
                                                @case('completed') bg-green-100 text-green-800 @break
                                                @case('pending') bg-yellow-100 text-yellow-800 @break
                                                @case('cancelled') bg-red-100 text-red-800 @break
                                                @default bg-gray-100 text-gray-800
                                            @endswitch">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="col-span-1">
                                    <dt class="text-gray-500">Dibayar</dt>
                                    <dd class="font-medium text-gray-900">Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</dd>
                                </div>
                                <div class="col-span-1">
                                    <dt class="text-gray-500">Kembalian</dt>
                                    <dd class="font-medium text-gray-900">Rp {{ number_format($transaction->change, 0, ',', '.') }}</dd>
                                </div>
                                @if($transaction->total_amount > $transaction->paid_amount)
                                <div class="col-span-2">
                                    <dt class="text-red-600">Kekurangan (dicatat sbg. hutang)</dt>
                                    <dd class="font-medium text-red-700">Rp {{ number_format($transaction->total_amount - $transaction->paid_amount, 0, ',', '.') }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white rounded-lg shadow">
                    <p class="text-gray-500">Tidak ada transaksi pada tanggal ini.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
