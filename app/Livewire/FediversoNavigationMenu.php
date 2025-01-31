<?php

namespace App\Livewire;

use Livewire\Component;

use App\ActivityPub\ActivityPub;
class FediversoNavigationMenu extends Component
{
    public function render()
    {
        $identidad=ActivityPub::GetIdentidad();
        return view('livewire.fediverso-navigation-menu',compact('identidad'));
    }
}
