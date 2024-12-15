<?php

namespace App\Livewire\Fediverso;

use Livewire\Component;
use Livewire\Attributes\On; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\ActivityPub\ActivityPub;

use Carbon\Carbon;


class Activity extends Component
{
    
    public $activity;
    public $user;
    public $origen=false;
    public $loading=true;
    public $diferido=true;

    public function mount($activity,$diferido=true)
    {
        $this->user = Auth::user();
        $this->activity = $activity;
        $this->diferido=$diferido;
        if (!$diferido) $this->cargar();
    }
    
    public function load()    
    {
        if ($this->diferido)
            $this->cargar();
    }
    public function cargar()    
    {
        $this->loading=false;
        if (isset($this->activity['error'])) $this->activity['type']="Error";
        if (isset($this->activity['actor']))
        if ($this->activity['type']=="Create")
        {
            if (is_string($this->activity['object']))    
                $this->activity=ActivityPub::GetObjectByUrl($this->user , $this->activity['object']);
            else
                $this->activity=$this->activity['object'];
        }
        if (isset($this->activity['actor']))
            if (is_string($this->activity['actor']))
                $this->activity['actor']=ActivityPub::GetActorByUrl($this->user , $this->activity['actor']);
        if (isset($this->activity['published'])) $this->activity['published']=Carbon::parse($this->activity['published']);
        if (isset($this->activity['object']))
            if (is_string($this->activity['object']))    $this->activity['object']=ActivityPub::GetObjectByUrl($this->user , $this->activity['object']);
        if (isset($this->activity['attributedTo']))
            if (is_string($this->activity['attributedTo']))
            {
                $this->activity['attributedTo']=ActivityPub::GetObjectByUrl($this->user , $this->activity['attributedTo']);
                if (!(isset($this->activity['attributedTo']['preferredUsername']))) $this->activity['error']='Error en attributedTo';
            }
        if (isset($this->activity['replies']))  $this->activity['replies']=ActivityPub::GetColeccion($this->user , $this->activity['replies'],true);
        if (isset($this->activity['likes']))  $this->activity['likes']=ActivityPub::GetColeccion($this->user , $this->activity['likes'],true);
        if (isset($this->activity['shares']))  $this->activity['shares']=ActivityPub::GetColeccion($this->user , $this->activity['shares'],true);
        if (isset($this->activity['inReplyTo']))  
        {
            if (is_string($this->activity['inReplyTo']))
                $this->activity['isreply']=ActivityPub::GetObjectByUrl($this->user , $this->activity['inReplyTo']);
            else
                $this->activity['isreply']=$this->activity['inReplyTo'];
            if (isset($this->activity['isreply']['attributedTo']))
            {
                $this->activity['autororigen']=ActivityPub::GetActorByUrl($this->user , $this->activity['isreply']['attributedTo']);
                if (isset($this->activity['autororigen']['preferredUsername']))
                {
                    $dom=explode("/",$this->activity['autororigen']['id']);
                    $this->activity['autororigen']=$this->activity['autororigen']['preferredUsername']."@".$dom[2];
                }
                else
                {   
                    unset($this->activity['isreply']);
                }
            }
            else
            {
                unset($this->activity['isreply']);
            }
        }

        if (isset($this->activity['content']))
            $this->activity['content']=ActivityPub::limpiarHtml($this->activity['content']);
        #if ($this->activity['type']=="Question")            Log::info(print_r($this->activity,1));
    }


    public function verorigen()
    {
        $this->origen=true;
    }

    public function render()
    {
        if (!(isset($this->activity['type']))) return "<div>no type</div>";
        if ((isset($this->activity['object']['error']))) return "<div>error</div>";
        return view('livewire.fediverso.activity', [
            'activity' => $this->activity,
            'origen' => $this->origen,
            'loading' => $this->loading,
        ]);
    }
}
