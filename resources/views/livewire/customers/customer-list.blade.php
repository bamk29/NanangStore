<div>
    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold text-gray-900">Manajemen Pelanggan</h1>
                <p class="mt-2 text-sm text-gray-700">Daftar semua pelanggan yang terdaftar beserta total poin dan hutang.</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <button wire:click="showCreateModal" type="button" class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">+ Tambah Pelanggan</button>
            </div>
        </div>

        <!-- Pesan Sukses/Error -->
        @if (session()->has('message'))
            <div class="mt-4 rounded-md bg-green-50 p-4">
                <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mt-4 rounded-md bg-red-50 p-4">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Filter -->
        <div class="mt-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari pelanggan berdasarkan nama atau no. telp..." class="input w-full">
        </div>

        <!-- Tabel Pelanggan -->
        <div class="-mx-4 mt-8 overflow-x-auto shadow ring-1 ring-black ring-opacity-5 sm:-mx-6 md:mx-0 md:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 cursor-pointer" wire:click="sortBy('name')">
                            Nama
                            @if($sortField === 'name')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 md:table-cell cursor-pointer" wire:click="sortBy('created_at')">
                            Tgl. Bergabung
                            @if($sortField === 'created_at')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">No. Telepon</th>
                        <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell cursor-pointer" wire:click="sortBy('points')">
                            Poin
                            @if($sortField === 'points')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 cursor-pointer" wire:click="sortBy('transactions_count')">
                            Total Transaksi
                            @if($sortField === 'transactions_count')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 cursor-pointer" wire:click="sortBy('transactions_max_created_at')">
                            Terakhir Transaksi
                            @if($sortField === 'transactions_max_created_at')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 cursor-pointer" wire:click="sortBy('debt')">
                            Hutang
                            @if($sortField === 'debt')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="w-full max-w-0 py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:w-auto sm:max-w-none sm:pl-6">
                                {{ $customer->name }}
                                <dl class="font-normal lg:hidden"><dt class="sr-only">No. Telepon</dt><dd class="mt-1 truncate text-gray-700">{{ $customer->phone }}</dd></dl>
                            </td>
                            <td class="hidden px-3 py-4 text-sm text-gray-500 md:table-cell">{{ $customer->created_at->format('d M Y') }}</td>
                            <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">{{ $customer->phone }}</td>
                            <td class="hidden px-3 py-4 text-sm text-gray-500 sm:table-cell">{{ $customer->points }}</td>
                            <td class="px-3 py-4 text-sm text-gray-500">{{ $customer->transactions_count }}</td>
                            <td class="px-3 py-4 text-sm text-gray-500">
                                {{ $customer->transactions_max_created_at ? \Carbon\Carbon::parse($customer->transactions_max_created_at)->format('d M Y') : '-' }}
                            </td>
                            <td class="px-3 py-4 text-sm font-semibold {{ $customer->debt > 0 ? 'text-red-600' : 'text-gray-500' }}">Rp {{ number_format($customer->debt, 0, ',', '.') }}</td>
                            <td class="py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('pos.index', ['customer_id' => $customer->id]) }}" wire:navigate class="p-2 rounded-full bg-sky-100 text-sky-600 hover:bg-sky-200 hover:text-sky-800" title="Mulai Belanja">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    </a>
                                    <a href="{{ route('customers.transactions', $customer->id) }}" wire:navigate class="p-2 rounded-full bg-purple-100 text-purple-600 hover:bg-purple-200 hover:text-purple-800" title="Riwayat Transaksi">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                    </a>
                                    @if($customer->debt > 0)
                                        <button wire:click="openDebtModal({{ $customer->id }})" class="p-2 rounded-full bg-green-100 text-green-600 hover:bg-green-200 hover:text-green-800" title="Bayar Hutang">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        </button>
                                    @endif
                                    <button wire:click="showEditModal({{ $customer->id }})" class="p-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 hover:text-blue-800" title="Edit">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $customer->id }})" class="p-2 rounded-full bg-red-100 text-red-600 hover:bg-red-200 hover:text-red-800" title="Hapus">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada data pelanggan yang ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $customers->links() }}</div>
    </div>

        <!-- Modal Tambah/Edit Pelanggan Terpadu -->

        @if($showModal)

        <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">

            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="closeModal()">

                <h3 class="text-lg font-bold mb-4">{{ $customerId ? 'Ubah Data Pelanggan' : 'Tambah Pelanggan Baru' }}</h3>

                <form wire:submit="saveCustomer" class="space-y-4">

                    <div>

                        <label for="name">Nama Pelanggan</label>

                        <input id="name" type="text" wire:model="name" class="input w-full">

                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                    </div>

                    <div>

                        <label for="phone">No. Telepon (Opsional)</label>

                        <input id="phone" type="text" wire:model="phone" class="input w-full">

                        @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                    </div>

    

                    @if($customerId)

                    <div class="border-t pt-4 space-y-4">

                        <div>

                            <label for="debt">Hutang</label>

                            <input id="debt" type="number" wire:model="debt" class="input w-full">

                            @error('debt') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                        </div>

                        <div>

                            <label for="points">Poin</label>

                            <input id="points" type="number" wire:model="points" class="input w-full">

                            @error('points') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                        </div>

                    </div>

                    @endif

    

                    <div class="flex justify-end space-x-4 pt-4">

                        <button type="button" wire:click="closeModal" class="px-4 py-2 rounded-lg text-gray-600 bg-gray-100 hover:bg-gray-200">Batal</button>

                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Simpan</button>

                    </div>

                </form>

            </div>

        </div>

        @endif

    

        <!-- Modal Pembayaran Hutang -->

        @if($showDebtModal)

        <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">

            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="$wire.set('showDebtModal', false)">

                <h3 class="text-xl font-bold mb-4">Pembayaran Hutang</h3>

                @if($selectedCustomer)

                <form wire:submit="processDebtPayment" class="space-y-4">

                    <div>

                        <label class="font-semibold">Pelanggan:</label>

                        <p class="text-lg">{{ $selectedCustomer->name }}</p>

                    </div>

                    <div>

                        <label class="font-semibold">Total Hutang Saat Ini:</label>

                        <p class="text-2xl font-bold text-red-600">Rp {{ number_format($selectedCustomer->debt, 0, ',', '.') }}</p>

                    </div>

                    <div>

                        <label for="payment_amount">Jumlah Pembayaran</label>

                        <input id="payment_amount" type="number" wire:model="payment_amount" class="input w-full" placeholder="Masukkan jumlah bayar">

                        @error('payment_amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                    </div>

                    <div>

                        <label class="block text-sm font-medium text-gray-700">Metode Pembayaran</label>

                        <div class="mt-2 flex space-x-4">

                            <label class="flex items-center">

                                <input type="radio" wire:model="debt_payment_method" value="cash" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">

                                <span class="ml-2 text-sm text-gray-700">Cash</span>

                            </label>

                            <label class="flex items-center">

                                <input type="radio" wire:model="debt_payment_method" value="transfer" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">

                                <span class="ml-2 text-sm text-gray-700">Transfer</span>

                            </label>

                        </div>

                    </div>

                    <div class="flex justify-end space-x-4 pt-4">

                        <button type="button" @click="$wire.set('showDebtModal', false)" class="px-4 py-2 rounded-lg text-gray-600 bg-gray-100 hover:bg-gray-200">Batal</button>

                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600">Simpan Pembayaran</button>

                    </div>

                </form>

                @endif

            </div>

        </div>

        @endif

    

        <!-- Modal Konfirmasi Hapus -->
    @if($customerToDeleteId)
    <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-bold mb-2">Hapus Pelanggan?</h3>
            <p class="text-sm text-gray-600 mb-4">Anda yakin ingin menghapus pelanggan ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex justify-end space-x-4">
                <button wire:click="$set('customerToDeleteId', null)" class="px-4 py-2 rounded-lg text-gray-600 bg-gray-100 hover:bg-gray-200">Batal</button>
                <button wire:click="deleteCustomer" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Ya, Hapus</button>
            </div>
        </div>
    </div>
    @endif


</div>
