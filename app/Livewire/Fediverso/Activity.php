<?php

namespace App\Livewire\Fediverso;

use Livewire\Component;
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
    
    public function mount($activity)
    {
        $this->user = Auth::user();
        $this->activity = $activity;

    }
    
    public function load()    
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
                $this->activity['actor']=ActivityPub::GetObjectByUrl($this->user , $this->activity['actor']);
        if (isset($this->activity['published'])) $this->activity['published']=Carbon::parse($this->activity['published']);
        if (isset($this->activity['object']))
            if (is_string($this->activity['object']))    $this->activity['object']=ActivityPub::GetObjectByUrl($this->user , $this->activity['object']);
        if (isset($this->activity['attributedTo']))
            if (is_string($this->activity['attributedTo']))    
                $this->activity['attributedTo']=ActivityPub::GetObjectByUrl($this->user , $this->activity['attributedTo']);

        if (isset($this->activity['replies']))  $this->activity['replies']=$this->CountCollection($this->activity['replies']);
        if (isset($this->activity['likes']))  $this->activity['likes']=$this->CountCollection($this->activity['likes']);
        if (isset($this->activity['shares']))  $this->activity['shares']=$this->CountCollection($this->activity['shares']);
        if (isset($this->activity['inReplyTo']))  
        {
            $this->activity['isreply']=ActivityPub::GetObjectByUrl($this->user , $this->activity['inReplyTo']);
            #Log::info(print_r($this->activity['isreply'],1));
            if (isset($this->activity['isreply']['attributedTo']))
            {
                $this->activity['autororigen']=ActivityPub::GetActorByUrl($this->user , $this->activity['isreply']['attributedTo']);
                if (isset($this->activity['autororigen']['preferredUsername']))
                {
                    $dom=explode("/",$this->activity['autororigen']['id']);
                    $this->activity['autororigen']=$this->activity['autororigen']['preferredUsername']."@".$dom[4];
                    Log::info('Tiene username '.$this->activity['autororigen']);
                }
                else
                {   
                    Log::info("respuesta ".print_r($this->activity['autororigen'],1));
                    Log::info('No tiene username '.$this->activity['isreply']['attributedTo']);
                    unset($this->activity['isreply']);
                }
            }
            else
            {
                unset($this->activity['isreply']);
            }
            #Log::info(print_r($this->activity['autororigen'],1));
        }
                
        
        if (isset($this->activity['type']))
        {
            if ($this->activity['type']=="Note")
            {
                if (!isset($this->activity['attributedTo']))
                    $this->activity['type']="Error 73843";
                $this->activity['content']=ActivityPub::limpiarHtml($this->activity['content']);
            }
        }
    }

    public function CountCollection($collection)
    {
        if (isset($collection['totalItems'])) return $collection['totalItems'];
        if (isset($collection['first'])) return count($collection['first']['items']);
        return "?";
    }

    public function verorigen()
    {
        $this->origen=true;
    }

    public function render()
    {
        if (!(isset($this->activity['type']))) return "<div></div>";

        return view('livewire.fediverso.activity', [
            'activity' => $this->activity,
            'origen' => $this->origen,
            'loading' => $this->loading,
        ]);
    }
}
