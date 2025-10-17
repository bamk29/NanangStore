<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryList extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryToDelete = null;

    public $category_id;
    public $perPage = 10;
    protected $paginationTheme = 'tailwind';

    public function deleteCategory()
    {
        if ($this->categoryToDelete) {
            $category = Category::find($this->categoryToDelete);
            if ($category && $category->products()->count() === 0) {
                $category->delete();
                $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Kategori berhasil dihapus.']);
            } else {
                $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Kategori tidak dapat dihapus karena memiliki produk terkait.']);
            }
        }
        $this->categoryToDelete = null;
        $this->dispatch('categoryDeleted'); // To close modal or refresh data
    }

    public function render()
    {
        $categories = Category::where('name', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.categories.category-list', [
            'categories' => $categories
        ]);
    }

    public function edit($category_id)
    {
        return redirect()->route('categories.edit', ['category_id' => $category_id]);
    }
}
