<div>
    <div class="sm:flex sm:items-center mb-4">
        <div class="sm:flex-auto">
            <h3 class="text-base font-semibold leading-6 text-gray-900">Riwayat Stok</h3>
            <p class="mt-2 text-sm text-gray-700">Detail pergerakan stok untuk produk ini.</p>
        </div>
    </div>
    
    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Tanggal</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Keterangan</th>
                    <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Tipe</th>
                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Jml</th>
                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Saldo</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">User</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($movements as $movement)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">
                            {{ $movement->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-3 py-4 text-sm text-gray-500">
                            <div class="font-medium text-gray-900">{{ $movement->description }}</div>
                            
                            @if($movement->type === 'sale' && $movement->transaction)
                                <div class="text-xs text-blue-600 mt-0.5">
                                    <a href="{{ route('pos.invoice', $movement->transaction->id) }}" target="_blank" class="hover:underline">
                                        {{ $movement->transaction->invoice_number }}
                                    </a>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <span class="font-semibold">Cust:</span> {{ $movement->transaction->customer->name ?? 'Umum' }}
                                </div>
                            @elseif($movement->type === 'purchase' && $movement->goodsReceipt)
                                <div class="text-xs text-blue-600 mt-0.5">
                                    {{-- Assuming we might have a GR view link later --}}
                                    {{ $movement->goodsReceipt->receipt_number }}
                                </div>
                                @if($movement->goodsReceipt->supplier)
                                <div class="text-xs text-gray-500">
                                    <span class="font-semibold">Supp:</span> {{ $movement->goodsReceipt->supplier->name }}
                                </div>
                                @endif
                            @elseif($movement->reference_id)
                                <div class="text-xs text-gray-400">Ref: {{ $movement->reference_id }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-4 text-center text-sm">
                            @php
                                $badges = [
                                    'sale' => 'bg-green-100 text-green-800',
                                    'purchase' => 'bg-blue-100 text-blue-800',
                                    'adjustment' => 'bg-yellow-100 text-yellow-800',
                                    'return' => 'bg-red-100 text-red-800',
                                    'correction' => 'bg-purple-100 text-purple-800',
                                    'item_add' => 'bg-indigo-100 text-indigo-800',
                                    'item_remove' => 'bg-pink-100 text-pink-800',
                                ];
                                $class = $badges[$movement->type] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $class }}">
                                {{ ucfirst(str_replace('_', ' ', $movement->type)) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-right text-sm font-medium">
                            @if($movement->quantity > 0)
                                <span class="text-green-600">+{{ number_format($movement->quantity, 0, ',', '.') }}</span>
                            @elseif($movement->quantity < 0)
                                <span class="text-red-600">{{ number_format($movement->quantity, 0, ',', '.') }}</span>
                            @else
                                <span class="text-gray-500">0</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-right text-sm text-gray-900">
                            {{ number_format($movement->stock_after, 0, ',', '.') }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                            {{ $movement->user->name ?? 'System' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-4 text-sm text-gray-500 text-center">
                            Belum ada riwayat stok.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $movements->links() }}
    </div>
</div>
