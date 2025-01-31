<?php

namespace App\Livewire\Fediverso;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

use App\ActivityPub\ActivityPub;

class Seguir extends Component
{
    public $actor;
    public $siguiendo;

    public function mount($actor)    
    {
        $this->actor=$actor;
        $this->siguiendo=$this->siguiendo();
    }

    public function siguiendo()
    {
        $user=ActivityPub::GetIdentidad();
        if ($user)
            return ActivityPub::siguiendo($user, $this->actor);
        return false;
    }

    public function dejarDeSeguir()
    {
        $user=ActivityPub::GetIdentidad();
        if ($user)
            if (ActivityPub::dejarDeSeguir($user, $this->actor))
                $this->siguiendo=false;
    }

    public function seguir()
    {
        $user=ActivityPub::GetIdentidad();
        if ($user)
            if (ActivityPub::seguir($user, $this->actor))
                $this->siguiendo=true;
    }

    public function render()
    {
        return view('livewire.fediverso.seguir', ['actor' => $this->actor]);
    }
}
