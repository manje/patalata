<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Category;

class CategoriesSelector extends Component
{
    public $categoryName;
    public $required;
    public $listcategories;
    public $selectedCategories;

    public function mount($required=true,$categoryName="Categorias",$selectedCategories=[])
    {
        $this->categoryName = $categoryName;
        $this->required = $required;
        $this->listcategories= Category::all();
        $this->selectedCategories = $selectedCategories;
    }

    public function changeCategory($category_id,$value)
    {
        $this->dispatch('changeCategory',$category_id,$value);
    }



    public function render()
    {
        return view('livewire.categories-selector', [
            'listcategories' => $this->listcategories,
            'categoryName' => $this->categoryName,
            'required' => $this->required,
            'selectedCategories' => $this->selectedCategories,
        ]);
    }
}
