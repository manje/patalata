<?php

namespace App\Livewire\Fediverso;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

use App\ActivityPub\ActivityPub;

class Seguir extends Component
{
    public $actor;
    public $siguiendo;
    protected $identidad;
    protected $ap;

    public function mount($actor)    
    {
        if (is_string($actor)) 
            $this->actor=$this->ap->GetActorByUrl($actor);
        else
            $this->actor=$actor;
        $this->siguiendo=$this->siguiendo();
    }

    public function siguiendo()
    {
        $this->identidad=ActivityPub::GetIdentidad();
        $this->ap=new ActivityPub($this->identidad);
        if ($this->identidad)
            return $this->ap->siguiendo($this->actor);
        return false;
    }

    public function dejarDeSeguir()
    {
        $this->identidad=ActivityPub::GetIdentidad();
        $this->ap=new ActivityPub($this->identidad);
        if ($this->identidad)
        if ($this->ap->dejarDeSeguir($this->actor))
                $this->siguiendo=false;
    }

    public function seguir()
    {
        $this->identidad=ActivityPub::GetIdentidad();
        $this->ap=new ActivityPub($this->identidad);
        if ($this->identidad)
            if ($this->ap->seguir($this->actor))
                $this->siguiendo=true;
    }

    public function render()
    {
        return view('livewire.fediverso.seguir', ['actor' => $this->actor,'siguiendo' => $this->siguiendo]);
    }
}
