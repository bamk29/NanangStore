<div class="relative">
    {{-- Action Buttons --}}
    <div class="fixed top-0 left-0 right-0 z-50 bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div class="flex space-x-4">
                    <button wire:click="backToPos"
                        class="px-6 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 active:scale-95 transition-all duration-150">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Kembali</span>
                        </div>
                    </button>
                </div>
                <div class="flex space-x-4">
                    <button wire:click="print"
                        class="px-6 py-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600 active:scale-95 transition-all duration-150">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Cetak</span>
                        </div>
                    </button>
                    <button wire:click="newTransaction"
                        class="px-6 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 active:scale-95 transition-all duration-150">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span>Transaksi Baru</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Invoice Content --}}
    <div class="bg-white w-[302px] mx-auto mt-24 mb-8 p-2 shadow-lg rounded-lg" id="invoice">
        {{-- Store Header --}}
        <div class="text-center border-b border-gray-300 pb-2">
            <h1 class="text-xl font-bold">NanangStore</h1>
            <p class="text-[12px]">Jl. Example Street No. 123</p>
            <p class="text-[12px]">Phone: (123) 456-7890</p>
        </div>

        {{-- Invoice Info --}}
        <div class="text-[12px] py-2 border-b border-gray-300">
            <div class="flex justify-between">
                <span>No: {{ $transaction->invoice_number }}</span>
                <span>{{ $transaction->created_at->format('d/m/y') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Time:</span>
                <span>{{ $transaction->created_at->format('H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Cashier:</span>
                <span>{{ auth()->user()->name }}</span>
            </div>
            @if ($transaction->customer_name)
                <div class="flex justify-between">
                    <span>Customer:</span>
                    <span>{{ $transaction->customer_name }}</span>
                </div>
            @endif
        </div>

        {{-- Items --}}
        <div class="text-[12px] py-2 border-b border-gray-300">
            <div class="border-b border-gray-300 pb-1">
                <div class="grid grid-cols-12">
                    <div class="col-span-5 font-semibold">Item</div>
                    <div class="col-span-2 text-right font-semibold">Qty</div>
                    <div class="col-span-5 text-right font-semibold">Total</div>
                </div>
            </div>

            @foreach ($transaction->details as $detail)
                <div class="py-1">
                    <div class="grid grid-cols-12">
                        <div class="col-span-12">{{ $detail->product->name }}</div>
                        <div class="col-span-7">{{ number_format($detail->price, 0, ',', '.') }} x
                            {{ $detail->quantity }}</div>
                        <div class="col-span-5 text-right">{{ number_format($detail->subtotal, 0, ',', '.') }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Totals --}}
        <div class="text-[12px] py-2 space-y-1 border-b border-gray-300">
            <div class="flex justify-between font-bold">
                <span>TOTAL</span>
                <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>CASH</span>
                <span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>CHANGE</span>
                <span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-[10px]">
                <span>Payment:</span>
                <span>{{ ucfirst($transaction->payment_method) }}</span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center text-[12px] pt-2 space-y-1">
            <p>{{ now()->format('d/m/Y H:i:s') }}</p>
            <p class="font-semibold">-- Thank You --</p>
            <p class="text-[10px]">#{{ $transaction->invoice_number }}</p>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {
                @this.on('printInvoice', () => {
                    window.print();
                    setTimeout(() => {
                        window.location.href = '{{ route('pos.index') }}';
                    }, 1000);
                });
            });
        </script>
    @endpush



    <div class="mt-3 flex justify-end gap-2">
        <button class="bg-gray-300 px-3 py-1 rounded" @click="showInvoice = false">Tutup</button>
        <button class="bg-green-500 text-white px-3 py-1 rounded" @click="alert('Transaksi disimpan!')">Simpan</button>
    </div>

</div>
