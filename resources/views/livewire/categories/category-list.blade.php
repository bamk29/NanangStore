<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold">Categories</h2>
        <a href="{{ route('categories.create') }}"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
            Add Category
        </a>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4">
        <input wire:model.live="search" type="text" placeholder="Search categories..."
            class="w-full md:w-1/3 px-4 py-2 border rounded-lg">
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($categories as $category)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $category->name }}</td>
                        <td class="px-6 py-4">{{ $category->description }}</td>
                        <td class="px-6 py-4">{{ $category->products->count() }}</td>
                        <td class="px-6 py-4 text-right text-sm">
                            <button wire:click="edit({{ $category->id }})"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit</button>
                            <button wire:click="$set('categoryToDelete', {{ $category->id }})"
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $categories->links() }}
    </div>

    @if($categoryToDelete)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg max-w-sm mx-auto">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Delete Category</h3>
                <p class="text-sm text-gray-500 mb-4">Are you sure you want to delete this category? This action cannot be undone.</p>
                <div class="flex justify-end gap-4">
                    <button wire:click="$set('categoryToDelete', null)"
                        class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300">
                        Cancel
                    </button>
                    <button wire:click="deleteCategory"
                        class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
