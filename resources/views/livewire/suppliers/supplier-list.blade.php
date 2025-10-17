<div>
    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Supplier</h1>
                <p class="mt-2 text-sm text-gray-700">Daftar semua supplier untuk kebutuhan purchase order.</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <button wire:click="create()" class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                    Tambah Supplier
                </button>
            </div>
        </div>

        <!-- Search and Table -->
        <div class="mt-8">
            <div class="mb-4">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama supplier..." class="block w-full md:w-1/3 rounded-md border-gray-300 shadow-sm">
            </div>

            <div class="-mx-4 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:-mx-6 md:mx-0 md:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Nama</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Kontak</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Alamat</th>
                            <th class="relative py-3.5 pl-3 pr-4 sm:pr-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($suppliers as $supplier)
                            <tr>
                                <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $supplier->name }}</td>
                                <td class="px-3 py-4 text-sm text-gray-500">
                                    <div>{{ $supplier->phone }}</div>
                                    <div class="text-xs">{{ $supplier->email }}</div>
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-500">{{ $supplier->address }}</td>
                                <td class="py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button wire:click="edit({{ $supplier->id }})" class="p-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 hover:text-blue-800" title="Edit">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $supplier->id }})" class="p-2 rounded-full bg-red-100 text-red-600 hover:bg-red-200 hover:text-red-800" title="Hapus">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada supplier ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4">
                    {{ $suppliers->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($isModalOpen)
    <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg" @click.away="closeModal()">
            <h3 class="text-lg font-bold mb-4">{{ $supplierId ? 'Edit Supplier' : 'Tambah Supplier Baru' }}</h3>
            <form wire:submit.prevent="store">
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Supplier</label>
                        <input type="text" wire:model.defer="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">No. Telepon</label>
                        <input type="text" wire:model.defer="phone" id="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" wire:model.defer="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea wire:model.defer="address" id="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                        @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-4">
                    <button type="button" wire:click="closeModal()" class="px-4 py-2 rounded-lg text-gray-600 bg-gray-100 hover:bg-gray-200">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($isDeleteModalOpen)
    <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-bold mb-2">Hapus Supplier?</h3>
            <p class="text-sm text-gray-600 mb-4">Anda yakin ingin menghapus supplier ini? Aksi ini tidak dapat diurungkan.</p>
            <div class="flex justify-end space-x-4">
                <button wire:click="$set('isDeleteModalOpen', false)" class="px-4 py-2 rounded-lg text-gray-600 bg-gray-100 hover:bg-gray-200">Tidak</button>
                <button wire:click="deleteSupplier()" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Ya, Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>
