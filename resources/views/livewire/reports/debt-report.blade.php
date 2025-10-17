<div>
    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-bold text-gray-900">Laporan Hutang Pelanggan</h1>
                <p class="mt-2 text-sm text-gray-700">Daftar semua pelanggan dengan sisa hutang yang belum lunas.</p>
            </div>
        </div>

        <!-- Summary Metrics -->
        <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <h3 class="text-sm font-medium text-gray-500 truncate">Total Piutang</h3>
                    <p class="mt-1 text-2xl font-semibold text-red-600">Rp {{ number_format($totalDebt, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <h3 class="text-sm font-medium text-gray-500 truncate">Jumlah Pelanggan Berhutang</h3>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $customers->total() }}</p>
                </div>
            </div>
        </div>

        <!-- Search and Table -->
        <div class="mt-8">
            <div class="mb-4">
                <input wire:model.debounce.300ms="search" type="text" placeholder="Cari nama atau nomor telepon pelanggan..." class="block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <div class="-mx-4 overflow-x-auto shadow ring-1 ring-black ring-opacity-5 sm:-mx-6 md:mx-0 md:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Nama Pelanggan</th>
                            <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">No. Telepon</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Jumlah Hutang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($customers as $customer)
                            <tr>
                                <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $customer->name }}</td>
                                <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">{{ $customer->phone ?? '-' }}</td>
                                <td class="px-3 py-4 text-sm font-medium text-red-600">Rp {{ number_format($customer->debt, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">
                                    @if(empty($search))
                                        Tidak ada pelanggan yang memiliki hutang.
                                    @else
                                        Pelanggan dengan nama "{{ $search }}" tidak ditemukan atau tidak memiliki hutang.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</div>
