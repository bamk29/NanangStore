<div class="h-screen flex flex-col">
    <div class="p-4 sm:p-6 lg:p-8 flex-shrink-0">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold text-gray-900">Transaksi Tertunda</h1>
                <p class="mt-2 text-sm text-gray-700">Pilih transaksi untuk dilanjutkan atau dibatalkan.</p>
            </div>
        </div>

        <!-- Minimalist Summary -->
        <div class="mt-6 text-sm text-gray-700 flex flex-wrap gap-x-6 gap-y-2">
            <p>
                <span class="font-semibold text-gray-900">{{ $total_pending_transactions }}</span> Transaksi Tertunda
            </p>
            <p>
                Total Nilai: <span class="font-semibold text-gray-900">Rp {{ number_format($total_pending_amount, 0, ',', '.') }}</span>
            </p>
            <p>
                Stok Tertunda ({{ $product_2_name }}): <span class="font-semibold text-gray-900">{{ number_format($product_2_quantity, 2, ',', '.') }} Kg</span>
            </p>
        </div>
    </div>

    <div class="flex-grow p-4 sm:p-6 lg:p-8 bg-gray-100 overflow-y-auto">
        @if($transactions->isEmpty())
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada transaksi tertunda</h3>
                    <p class="mt-1 text-sm text-gray-500">Semua transaksi sudah selesai atau dibatalkan.</p>
                </div>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                @foreach ($transactions as $transaction)
                    <div class="bg-white rounded-xl shadow-md p-4 flex flex-col h-full text-center">
                        <div class="flex-grow">
                            <p class="text-xs text-gray-500">{{ $transaction->created_at->diffForHumans() }}</p>
                            <p class="mt-2 font-semibold text-gray-800 truncate">{{ $transaction->customer->name ?? 'Pelanggan Umum' }}</p>
                            <p class="mt-1 text-lg font-bold text-blue-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-100 flex space-x-2">
                            <a href="{{ route('pos.index', ['resume' => $transaction->id]) }}"
                               class="flex-1 text-center px-3 py-2 bg-blue-500 text-white rounded-lg text-sm font-semibold hover:bg-blue-600 transition-colors duration-200">
                                Lanjutkan
                            </a>
                            <button wire:click="confirmCancel({{ $transaction->id }})"
                               class="flex-1 text-center px-3 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors duration-200">
                                Batalkan
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Modal Konfirmasi Batal -->
    @if($transaction_to_cancel)
    <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4" x-data x-cloak>
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="$wire.set('transaction_to_cancel', null)">
            <h3 class="text-lg font-bold mb-2">Batalkan Transaksi?</h3>
            <p class="text-sm text-gray-600 mb-4">Anda yakin ingin membatalkan transaksi ini? Aksi ini tidak dapat diurungkan.</p>
            <div class="flex justify-end space-x-4">
                <button @click="$wire.set('transaction_to_cancel', null)" class="px-4 py-2 rounded-lg text-gray-600 bg-gray-100 hover:bg-gray-200">Tidak</button>
                <button wire:click="cancelTransaction" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Ya, Batalkan</button>
            </div>
        </div>
    </div>
    @endif
</div>