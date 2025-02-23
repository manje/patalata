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
        Log::info(print_r($this->actor,1));
        if (is_string($actor)) 
            $this->actor=ActivityPub::GetActorByUrl(ActivityPub::GetIdentidad(), $actor);
        else
            $this->actor=$actor;
        Log::info(print_r($this->actor,1));
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
        return view('livewire.fediverso.seguir', ['actor' => $this->actor,'siguiendo' => $this->siguiendo]);
    }
}
