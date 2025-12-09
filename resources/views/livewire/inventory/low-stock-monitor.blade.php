<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold text-gray-800">Monitor Stok Menipis</h1>
            <a href="{{ route('purchase-orders.index') }}" wire:navigate class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 border border-gray-300 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                Lihat Semua PO
            </a>
        </div>
        <div class="flex gap-2">
            @if(count($selectedProducts) > 0)
                <button wire:click="generatePurchaseOrders" 
                    wire:confirm="Yakin ingin membuat PO untuk {{ count($selectedProducts) }} produk terpilih?"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Buat PO (Draft)
                </button>
            @endif
        </div>
    </div>

    <!-- Threshold Legend -->
    <div class="flex gap-4 mb-4 text-sm">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-red-500"></span>
            <span>Bahaya (≤ 5) - Wajib Belanja</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-orange-500"></span>
            <span>Warning (≤ 10)</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
            <span>Siap-siap (≤ 15)</span>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-4 w-10">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="p-4 font-semibold text-gray-600">Produk</th>
                        <th class="p-4 font-semibold text-gray-600">Supplier</th>
                        <th class="p-4 font-semibold text-gray-600 text-center">Stok</th>
                        <th class="p-4 font-semibold text-gray-600 text-right">Harga Beli</th>
                        <th class="p-4 font-semibold text-gray-600 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                        @php
                            $status = $this->getStockStatus($product->stock);
                            $rowClass = match($status) {
                                'danger' => 'bg-red-50',
                                'urgent' => 'bg-orange-50',
                                default => 'hover:bg-gray-50'
                            };
                            $badgeClass = match($status) {
                                'danger' => 'bg-red-100 text-red-800',
                                'urgent' => 'bg-orange-100 text-orange-800',
                                'warning' => 'bg-yellow-100 text-yellow-800',
                                default => 'bg-green-100 text-green-800'
                            };
                            $badgeText = match($status) {
                                'danger' => 'KRITIS',
                                'urgent' => 'MENIPIS',
                                'warning' => 'WASPADA',
                                default => 'AMAN'
                            };
                        @endphp
                        <tr class="{{ $rowClass }} transition-colors">
                            <td class="p-4">
                                <input type="checkbox" value="{{ $product->id }}" wire:model.live="selectedProducts" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="p-4">
                                <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                <div class="text-xs text-gray-500">{{ $product->code }}</div>
                            </td>
                            <td class="p-4">
                                @if($product->supplier)
                                    <span class="text-gray-700">{{ $product->supplier->name }}</span>
                                @else
                                    <span class="text-gray-400 italic">Tanpa Supplier</span>
                                @endif
                            </td>
                            <td class="p-4 text-center font-bold text-lg">
                                {{ $product->stock }}
                            </td>
                            <td class="p-4 text-right font-mono">
                                {{ number_format($product->unit_cost, 0, ',', '.') }}
                            </td>
                            <td class="p-4 text-right">
                                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $badgeClass }}">
                                    {{ $badgeText }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="font-medium">Stok Aman!</p>
                                    <p class="text-sm">Tidak ada produk di bawah batas peringatan (15 item).</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t bg-gray-50">
            {{ $products->links() }}
        </div>
    </div>
</div>
