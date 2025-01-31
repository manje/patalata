<?php

namespace App\Livewire\Profile;

use Livewire\Component;

use App\Models\Place;

class Categories extends Component
{
    public $place_id;
    public $categories=[];
    public $places;
    
    protected $listeners = ['changeCategory'=>'changeCategory'];

    public function municipioSelected($municipio_id)
    {
        $this->municipio_id = $municipio_id;
    }

    public function changeCategory($category_id,$value="mal")
    {
        if ($value)
            $this->categories[]=$category_id;
        else 
            $this->categories=array_diff($this->categories,[$category_id]);
    }

    public function mount()    
    {
        $this->place_id = auth()->user()->place_id;
        $this->places=Place::all();
        $this->categories=auth()->user()->categories->pluck('id')->toArray();
    }

    public function updateMunAndInt()
    {
        $user=auth()->user();
        $user->categories()->sync($this->categories);
        $user->place_id=$this->place_id;
        $user->save();
        $this->dispatch('saved');
        $this->dispatch('refresh-navigation-menu');
    }

    public function render()
    {
        return view('livewire.profile.categories');                                                                                                                         
    }
}
