<div>
    <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg p-6">
        <h2 class="text-2xl font-semibold mb-4">Riwayat Transaksi</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label for="selectedDate" class="block text-sm font-medium text-gray-700">Pilih Tanggal</label>
                <input type="date" id="selectedDate" wire:model.live="selectedDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700">Cari (No. Invoice / Nama Pelanggan)</label>
                <input type="text" id="search" wire:model.live.debounce.300ms="search" placeholder="Ketik untuk mencari..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kasir</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $transaction->invoice_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->created_at->format('H:i:s') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->customer->name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->user->name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @switch($transaction->status)
                                        @case('completed') bg-green-100 text-green-800 @break
                                        @case('pending') bg-yellow-100 text-yellow-800 @break
                                        @case('cancelled') bg-red-100 text-red-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('pos.invoice', $transaction->id) }}" wire:navigate="true" class="text-indigo-600 hover:text-indigo-900">Cetak Ulang</a>
                                @if ($transaction->status == 'completed')
                                <span class="text-gray-300 mx-1">|</span>
                                <button wire:click="openPaymentModal({{ $transaction->id }})" class="text-blue-600 hover:text-blue-900">Ubah Pembayaran</button>
                                <span class="text-gray-300 mx-1">|</span>
                                <button wire:click="correctTransaction({{ $transaction->id }})" wire:confirm="Anda yakin ingin mengoreksi transaksi ini? Transaksi lama akan dibatalkan dan keranjang akan diisi kembali untuk diperbaiki." class="text-green-600 hover:text-green-900">Koreksi</button>
                                <span class="text-gray-300 mx-1">|</span>
                                <button wire:click="cancelTransaction({{ $transaction->id }})" wire:confirm="Apakah Anda yakin ingin membatalkan transaksi ini? Stok, hutang, dan data keuangan terkait akan dikembalikan ke kondisi semula."
                                    class="text-red-600 hover:text-red-900">Batalkan</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada transaksi pada tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- Edit Payment Modal -->
    <div x-data="{ show: $wire.entangle('showPaymentModal') }" x-show="show" x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div @click.away="show = false" class="bg-white rounded-lg shadow-xl w-full max-w-2xl transform transition-all"
             x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            @if ($editingTransaction)
                <div class="p-5 border-b">
                    <h3 class="text-lg font-bold text-gray-800">Ubah Pembayaran: {{ $editingTransaction->invoice_number }}</h3>
                </div>

                <div class="p-5 space-y-5">
                    <div class="text-center">
                        <p class="text-gray-500 text-sm">Total Tagihan</p>
                        <p class="text-3xl font-bold text-gray-900">Rp {{ number_format($editingTransaction->total_amount, 0, ',', '.') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Metode Pembayaran</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="p-3 border rounded-lg text-center cursor-pointer select-none transition" :class="{ 'bg-blue-600 text-white border-blue-600': $wire.payment_method === 'cash', 'bg-gray-100 hover:bg-gray-200 text-gray-700': $wire.payment_method !== 'cash' }">
                                <input type="radio" class="hidden" wire:model="payment_method" value="cash">
                                <span>Tunai</span>
                            </label>
                            <label class="p-3 border rounded-lg text-center cursor-pointer select-none transition" :class="{ 'bg-blue-600 text-white border-blue-600': $wire.payment_method === 'transfer', 'bg-gray-100 hover:bg-gray-200 text-gray-700': $wire.payment_method !== 'transfer' }">
                                <input type="radio" class="hidden" wire:model="payment_method" value="transfer">
                                <span>Transfer</span>
                            </label>
                            <label class="p-3 border rounded-lg text-center cursor-pointer select-none transition" :class="{ 'bg-blue-600 text-white border-blue-600': $wire.payment_method === 'debt', 'bg-gray-100 hover:bg-gray-200 text-gray-700': $wire.payment_method !== 'debt' }">
                                <input type="radio" class="hidden" wire:model="payment_method" value="debt">
                                <span>Hutang</span>
                            </label>
                        </div>
                    </div>

                    <div x-show="$wire.payment_method !== 'debt'">
                        <label class="block text-sm font-semibold mb-2">Uang Dibayarkan</label>
                        <input type="number" wire:model.live="paid_amount" placeholder="0" class="w-full border rounded-lg p-2 font-bold text-lg text-right focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                        @error('paid_amount') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Catatan</label>
                        <textarea wire:model="notes" rows="2" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none" placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>

                <div class="p-5 border-t bg-gray-50 flex justify-end gap-3">
                    <button @click="show = false" type="button" class="px-4 py-2 rounded-lg text-gray-700 bg-gray-200 hover:bg-gray-300">Batal</button>
                    <button wire:click="updatePayment" type="button" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Simpan Perubahan
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
