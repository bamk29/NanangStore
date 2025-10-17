<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Livewire\Component;
use Illuminate\Validation\Rule;

class CreateCategory extends Component
{
    public $categoryId;
    public $name = '';
    public $description = '';

    public $category_id;

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'min:2',
                'max:255',
                Rule::unique('categories', 'name')->ignore($this->categoryId),
            ],
            'description' => 'nullable|max:1000',
        ];
    }

    public function mount($category_id = null)
    {
        if ($category_id) {
            $category = Category::findOrFail($category_id);
            $this->categoryId = $category->id;
            $this->name = $category->name;
            $this->description = $category->description;
        }
    }

    public function save()
    {
        $this->validate();

        Category::updateOrCreate(
            ['id' => $this->categoryId],
            [
                'name' => $this->name,
                'description' => $this->description,
            ]
        );

        session()->flash('message', $this->categoryId ? 'Kategori berhasil diperbarui.' : 'Kategori berhasil dibuat.');

        return redirect()->route('categories.index');
    }

    public function render()
    {
        return view('livewire.categories.create-category');
    }
}
