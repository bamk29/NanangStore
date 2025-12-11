<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Riwayat Transaksi: {{ $customer->name }}</h1>
            <p class="text-gray-600">Total Poin: {{ number_format($customer->points) }} | Hutang: Rp {{ number_format($customer->debt, 0, ',', '.') }}</p>
        </div>
        <a href="{{ route('customers.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Top Products Section -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Produk Paling Sering Dibeli</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3">Produk</th>
                            <th scope="col" class="px-4 py-3 text-center">Total Qty</th>
                            <th scope="col" class="px-4 py-3 text-center">Frekuensi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $productStat)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $productStat->product->name ?? 'Produk Dihapus' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($productStat->total_quantity) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($productStat->frequency) }}x
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-center">Belum ada data pembelian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Stats (Optional, can be expanded) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Ringkasan</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-sm text-blue-600 font-medium">Total Transaksi</p>
                    <p class="text-2xl font-bold text-blue-800">{{ $transactions->total() }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <p class="text-sm text-green-600 font-medium">Total Pengeluaran</p>
                    <p class="text-2xl font-bold text-green-800">
                        Rp {{ number_format($transactions->sum('total_amount'), 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History Table -->
    <div x-data="{ activeTab: 'transactions' }" class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex">
                <button @click="activeTab = 'transactions'" 
                    :class="activeTab === 'transactions' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm">
                    Riwayat Invoice
                </button>
                <button @click="activeTab = 'ledger'" 
                    :class="activeTab === 'ledger' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm">
                    Riwayat Hutang (Buku Besar)
                </button>
            </nav>
        </div>

        <!-- Transactions Tab -->
        <div x-show="activeTab === 'transactions'">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Tanggal</th>
                            <th scope="col" class="px-6 py-3">No. Invoice</th>
                            <th scope="col" class="px-6 py-3">Total</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">Item</th>
                            <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    {{ $transaction->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $transaction->invoice_number }}
                                </td>
                                <td class="px-6 py-4">
                                    Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $transaction->status === 'paid' ? 'bg-green-100 text-green-800' :
                                           ($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs text-gray-500">
                                        {{ $transaction->details->count() }} item(s)
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('pos.invoice', $transaction->id) }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                                        Lihat Invoice
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center">Belum ada riwayat transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $transactions->links() }}
            </div>
        </div>

        <!-- Ledger Tab -->
        <div x-show="activeTab === 'ledger'" style="display: none;">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Tanggal</th>
                            <th scope="col" class="px-6 py-3">Keterangan</th>
                            <th scope="col" class="px-6 py-3 text-center">Mutasi</th>
                            <th scope="col" class="px-6 py-3 text-right">Saldo Hutang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ledgers as $ledger)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $ledger->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $ledger->description }}</div>
                                    @if($ledger->transaction)
                                        <div class="text-xs text-gray-500">Inv: {{ $ledger->transaction->invoice_number }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($ledger->type === 'increase')
                                        <span class="text-red-600 font-bold">+ {{ number_format($ledger->amount, 0, ',', '.') }}</span>
                                    @elseif($ledger->type === 'decrease')
                                        <span class="text-green-600 font-bold">- {{ number_format($ledger->amount, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-gray-600 font-bold">{{ number_format($ledger->amount, 0, ',', '.') }}</span>
                                    @endif
                                    <div class="text-xs text-gray-500 uppercase">{{ $ledger->type }}</div>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-800">
                                    Rp {{ number_format($ledger->balance_after, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center">Belum ada data history hutang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 text-xs text-gray-500 text-center">
                Menampilkan 100 riwayat hutang terakhir.
            </div>
        </div>
    </div>
</div>
