<div class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-3xl bg-white rounded-2xl shadow-lg p-6 md:p-10">
        
        <div class="text-center mb-8">
            <svg class="w-16 h-16 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">Transaksi Berhasil</h1>
            <p class="text-gray-500">No. Invoice: {{ $transaction->invoice_number }}</p>
        </div>

        <!-- Rincian Item -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-2">Rincian Pembelian</h2>
            <div class="space-y-2">
                @foreach($transaction->details as $detail)
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600">{{ $detail->product->name }} ({{ rtrim(rtrim(number_format($detail->quantity, 2, ',', '.'), '0'), ',') }} x {{ number_format($detail->price, 0, ',', '.') }})</span>
                    <span class="font-medium text-gray-800">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Ringkasan Pembayaran -->
        <div class="border-t border-gray-200 pt-6 mb-6">
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-medium text-gray-800">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold">
                    <span class="text-gray-800">TOTAL</span>
                    <span class="text-blue-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Bayar ({{ strtoupper($transaction->payment_method) }})</span>
                    <span class="font-medium text-gray-800">Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Kembali</span>
                    <span class="font-medium text-gray-800">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Info Pelanggan & Hutang -->
        @if($transaction->customer)
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h3 class="font-semibold text-gray-800">Info Pelanggan: {{ $transaction->customer->name }}</h3>
            @if($transaction->customer->debt > 0)
            <div class="mt-2 flex justify-between items-center text-red-600">
                <span class="text-sm font-medium">Total Hutang Saat Ini:</span>
                <span class="text-lg font-bold">Rp {{ number_format($transaction->customer->debt, 0, ',', '.') }}</span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Total hutang termasuk transaksi ini (jika hutang) dan transaksi sebelumnya.</p>
            @else
            <p class="text-sm text-gray-500 mt-1">Pelanggan ini tidak memiliki sisa hutang.</p>
            @endif
        </div>
        @endif

        <!-- Tombol Aksi -->
        <div class="mt-10 flex flex-col md:flex-row gap-3 justify-center">
            <button wire:click="newTransaction" class="w-full md:w-auto flex-1 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition-colors">
                Transaksi Baru
            </button>
            <a href="{{ route('print.receipt', $transaction) }}" target="_blank" class="w-full md:w-auto flex-1 text-center px-6 py-3 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                Print Struk (Kasir Bersama)
            </a>
            <a href="{{ route('print.receipt_nanang_store', $transaction) }}" target="_blank" class="w-full md:w-auto flex-1 text-center px-6 py-3 bg-green-200 text-green-800 font-semibold rounded-lg hover:bg-green-300 transition-colors">
                Print Struk (Toko Nanang)
            </a>
        </div>
    </div>
</div>

