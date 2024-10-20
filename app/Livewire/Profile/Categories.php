<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

class Categories extends Component
{
    public $municipio_id;
    public $categories=[];
    protected $listeners = ['municipioSelected'=>'municipioSelected','changeCategory'=>'changeCategory'];

    public function municipioSelected($municipio_id)
    {
        $this->municipio_id = $municipio_id;
    }

    public function changeCategory($category_id,$value="mal")
    {
        Log::info("xchangeCategory");
        Log::info("xcategory_id ".$category_id);
        Log::info("xvalue ".$value);
        if ($value)
            Log::info("xvalue true");
        if ($value)
            $this->categories[]=$category_id;
        else 
            $this->categories=array_diff($this->categories,[$category_id]);

        #else
        #    $this->categories=array_diff($this->categories,[$category_id]);
       
        #$user=auth()->user();
        #$this->categories=$user->categories;
        Log::info("categorias ".json_encode($this->categories));
    }

    public function mount()    
    {
        $this->municipio_id = auth()->user()->municipio_id;
        $this->categories=auth()->user()->categories->pluck('id')->toArray();
    }

    public function updateMunAndInt()
    {
        if ($this->municipio_id)
        {
            $user=auth()->user();
            $user->categories()->sync($this->categories);
            $user->municipio_id=$this->municipio_id;
            $user->save();
            $this->dispatch('saved');
            $this->dispatch('refresh-navigation-menu');
        }
    }

    public function render()
    {
        return view('livewire.profile.categories');    
    }
}
