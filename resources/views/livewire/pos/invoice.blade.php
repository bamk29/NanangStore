<div x-data x-on:keydown.window.prevent="
    (() => {
        switch ($event.key) {
            case '1': $wire.newTransaction(); break;
            case '2': $wire.printInvoice('invoice'); break;
            case '3': $wire.printInvoice('store'); break;
        }
    })();
" class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-3xl bg-white rounded-2xl shadow-lg p-6 md:p-10">

        <div class="text-center mb-8">
            <svg class="w-16 h-16 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">Transaksi Berhasil</h1>
            <p class="text-gray-500 flex items-center justify-center gap-2">
                No. Invoice: {{ $transaction->invoice_number }}
                <button @click="copyInvoiceToClipboard()" class="text-teal-600 hover:text-teal-800" title="Copy Struk">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                </button>
            </p>
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
                @php
                    $itemSubtotal = $transaction->details->sum('subtotal');
                    $includedOldDebt = $transaction->total_amount - $itemSubtotal + $transaction->total_reduction_amount;
                @endphp
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal Belanja</span>
                    <span class="font-medium text-gray-800">Rp {{ number_format($itemSubtotal, 0, ',', '.') }}</span>
                </div>

                @if($includedOldDebt > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Bayar Hutang Lama</span>
                    <span class="font-medium text-gray-800">Rp {{ number_format($includedOldDebt, 0, ',', '.') }}</span>
                </div>
                @endif

                @if($transaction->total_reduction_amount > 0)
                <div class="flex justify-between text-sm text-red-600">
                    <span class="text-red-600">Potongan Harga</span>
                    <span class="font-medium">- Rp {{ number_format($transaction->total_reduction_amount, 0, ',', '.') }}</span>
                </div>
                @if($transaction->reduction_notes)
                <div class="flex justify-end text-xs text-red-500 -mt-1">
                    <span>({{ $transaction->reduction_notes }})</span>
                </div>
                @endif
                @endif

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
                <div class="flex items-center gap-2">
                    <span class="text-lg font-bold">Rp {{ number_format($transaction->customer->debt, 0, ',', '.') }}</span>
                    @php
                        $debtFormatted = number_format($transaction->customer->debt, 0, ',', '.');
                    @endphp
                    <button @click='copyDebtToClipboard(@json($transaction->customer->name), @json($debtFormatted))' class="text-red-600 hover:text-red-800" title="Copy Tagihan">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                    </button>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-1">Total hutang termasuk transaksi ini (jika hutang) dan transaksi sebelumnya.</p>
            @else
            <p class="text-sm text-gray-500 mt-1">Pelanggan ini tidak memiliki sisa hutang.</p>
            @endif
        </div>
        @endif

        <!-- Tombol Aksi -->
        <div class="mt-10 flex flex-col md:flex-row gap-3 justify-center">
            <button wire:click="newTransaction" class="w-full md:w-auto flex-1 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                <span>Transaksi Baru</span>
                <kbd class="px-2 py-1 text-xs font-sans font-semibold text-gray-800 bg-gray-100 border border-gray-300 rounded-md">1</kbd>
            </button>
            <button wire:click="printInvoice('invoice')" wire:loading.attr="disabled" wire:target="printInvoice('invoice')" class="w-full md:w-auto flex-1 inline-flex items-center justify-center px-6 py-3 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                <span wire:loading wire:target="printInvoice('invoice')" class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full mr-2" role="status" aria-hidden="true"></span>
                <span>Print (Kasir Bersama)</span>
                <kbd class="ml-2 px-2 py-1 text-xs font-sans font-semibold text-gray-800 bg-gray-100 border border-gray-300 rounded-md">2</kbd>
            </button>
            <button wire:click="printInvoice('store')" wire:loading.attr="disabled" wire:target="printInvoice('store')" class="w-full md:w-auto flex-1 inline-flex items-center justify-center px-6 py-3 bg-green-200 text-green-800 font-semibold rounded-lg hover:bg-green-300 transition-colors">
                <span wire:loading wire:target="printInvoice('store')" class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full mr-2" role="status" aria-hidden="true"></span>
                <span>Print (Toko Nanang)</span>
                <kbd class="ml-2 px-2 py-1 text-xs font-sans font-semibold text-gray-800 bg-gray-100 border border-gray-300 rounded-md">3</kbd>
            </button>
            @if($transaction->customer && $transaction->customer->phone)
            <button @click='sendInvoiceToWhatsApp(@json($transaction->customer->phone))' class="w-full md:w-auto flex-1 inline-flex items-center justify-center px-6 py-3 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-8.683-2.031-9.672-.272-.989-.471-1.135-.644-1.135-.174 0-.371-.006-.57-.006-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/></svg>
                <span>Kirim Struk WA</span>
            </button>
            @else
            <button @click='sendInvoiceToWhatsApp(null)' class="w-full md:w-auto flex-1 inline-flex items-center justify-center px-6 py-3 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-8.683-2.031-9.672-.272-.989-.471-1.135-.644-1.135-.174 0-.371-.006-.57-.006-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/></svg>
                <span>Kirim Struk WA</span>
            </button>
            @endif
            @if($transaction->customer && $transaction->customer->debt > 0)
            @php
                $debtFormatted = number_format($transaction->customer->debt, 0, ',', '.');
            @endphp
            <button @click='sendDebtToWhatsApp(@json($transaction->customer->name), @json($debtFormatted), @json($transaction->customer->phone))' class="w-full md:w-auto flex-1 inline-flex items-center justify-center px-6 py-3 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-8.683-2.031-9.672-.272-.989-.471-1.135-.644-1.135-.174 0-.371-.006-.57-.006-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/></svg>
                <span>Kirim WA Hutang</span>
            </button>
            @endif
        </div>
    </div>
    <script>
        window.copyInvoiceToClipboard = function(returnText = false) {
            try {
                @php
                    $invoiceNumber = $transaction->invoice_number;
                    $date = $transaction->created_at->format('d/m/Y H:i');
                    $total = number_format($transaction->total_amount, 0, ',', '.');
                @endphp
                let invoiceNumber = @json($invoiceNumber);
                let date = @json($date);
                let total = @json($total);
                
                let text = `*STRUK PEMBELIAN*\n`;
                text += `*GILING BAKSO BINJAI BARU/Nanang.Mart*\n`;
                text += `No: ${invoiceNumber}\n`;
                text += `Tgl: ${date}\n`;
                text += `--------------------------------\n`;

                @foreach($transaction->details as $detail)
                @php
                    $productName = $detail->product->name;
                    $qty = rtrim(rtrim(number_format($detail->quantity, 2, ',', '.'), '0'), ',');
                    $price = number_format($detail->price, 0, ',', '.');
                    $subtotal = number_format($detail->subtotal, 0, ',', '.');
                @endphp
                text += `${@json($productName)}\n`;
                text += `${@json($qty)} x ${@json($price)} = ${@json($subtotal)}\n`;
                @endforeach

                text += `--------------------------------\n`;
                text += `*Total: Rp ${total}*\n`;
                
                @if($transaction->paid_amount > 0)
                @php
                    $paid = number_format($transaction->paid_amount, 0, ',', '.');
                    $change = number_format($transaction->change_amount, 0, ',', '.');
                @endphp
                text += `Bayar: Rp ${@json($paid)}\n`;
                text += `Kembali: Rp ${@json($change)}\n`;
                @endif

                @if($transaction->customer)
                text += `\nPelanggan: ${@json($transaction->customer->name)}\n`;
                @if($transaction->customer->debt > 0)
                @php
                    $debt = number_format($transaction->customer->debt, 0, ',', '.');
                @endphp
                text += `Sisa Hutang: Rp ${@json($debt)}\n`;
                @endif
                @endif

                text += `\nTerima Kasih! 🙏`;

                if (returnText) return text;
                copyText(text, 'Struk berhasil disalin! Silakan tempel di WhatsApp.');
            } catch (e) {
                alert('Error generating invoice text: ' + e.message);
            }
        }

        window.sendInvoiceToWhatsApp = function(customerPhone) {
            try {
                let text = copyInvoiceToClipboard(true);
                let encodedText = encodeURIComponent(text);
                let url = '';

                if (customerPhone) {
                    let phone = customerPhone.replace(/\D/g, '');
                    if (phone.startsWith('0')) {
                        phone = '62' + phone.substring(1);
                    }
                    url = `https://wa.me/${phone}?text=${encodedText}`;
                } else {
                    url = `https://wa.me/?text=${encodedText}`;
                }

                window.open(url, '_blank');
            } catch (e) {
                alert('Error opening WhatsApp: ' + e.message);
            }
        }

        window.copyDebtToClipboard = function(customerName, debtAmount) {
            try {
                if (!customerName) {
                    alert('Data pelanggan tidak ditemukan.');
                    return;
                }
                
                let text = generateDebtText(customerName, debtAmount);
                copyText(text, 'Teks penagihan berhasil disalin! Silakan tempel di WhatsApp.');
            } catch (e) {
                alert('Error generating debt text: ' + e.message);
            }
        }

        window.sendDebtToWhatsApp = function(customerName, debtAmount, customerPhone) {
            try {
                if (!customerName) {
                    alert('Data pelanggan tidak ditemukan.');
                    return;
                }
                
                let text = generateDebtText(customerName, debtAmount);
                let encodedText = encodeURIComponent(text);
                let url = '';

                if (customerPhone) {
                    // Format phone number: replace leading 0 with 62, remove non-digits
                    let phone = customerPhone.replace(/\D/g, '');
                    if (phone.startsWith('0')) {
                        phone = '62' + phone.substring(1);
                    }
                    url = `https://wa.me/${phone}?text=${encodedText}`;
                } else {
                    // If no phone, just open WA with text (user selects contact)
                    url = `https://wa.me/?text=${encodedText}`;
                }

                window.open(url, '_blank');
            } catch (e) {
                alert('Error opening WhatsApp: ' + e.message);
            }
        }

        function generateDebtText(customerName, debtAmount) {
            let text = `Halo Kak *${customerName}*,\n\n`;
            text += `Kami dari *Giling Bakso Binjai Baru/Nanang.Mart* ingin mengingatkan tagihan Anda yang *BELUM LUNAS*:\n\n`;
            text += `Total Hutang Kamu: *Rp ${debtAmount}*\n\n`;
            text += `Mohon *SEGERA* dilakukan pembayaran ya Kak, agar transaksi selanjutnya lancar. Ditunggu transfer/pembayarannya hari ini. Terima kasih. 🙏`;
            text += `Rejeki Akan Lancar dan Ringan jika Hutangmu segera dilunasi 🙏`;
            text += `Abaikan jika sudah lunas. 🙏`;
            return text;
        }

        window.copyText = function(text, successMessage) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    alert(successMessage);
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                    fallbackCopyTextToClipboard(text, successMessage);
                });
            } else {
                fallbackCopyTextToClipboard(text, successMessage);
            }
        }

        window.fallbackCopyTextToClipboard = function(text, successMessage) {
            var textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                var successful = document.execCommand('copy');
                if (successful) {
                    alert(successMessage);
                } else {
                    alert('Gagal menyalin teks (Fallback).');
                }
            } catch (err) {
                console.error('Fallback: Oops, unable to copy', err);
                alert('Gagal menyalin teks (Exception).');
            }
            document.body.removeChild(textArea);
        }
    </script>
</div>
