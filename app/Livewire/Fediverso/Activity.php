<?php

namespace App\Livewire\Fediverso;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\ActivityPub\ActivityPub;

class Activity extends Component
{
    public $activity;
    public $user;

    public function mount($activity)    
    {
        $this->user = Auth::user();
        $this->activity = $activity;
        if (isset($this->activity['error'])) $this->activity['type']="Error";
        if (isset($this->activity['actor']))
        if (is_string($this->activity['actor']))
            $this->activity['actor']=ActivityPub::GetObjectByUrl($this->user , $this->activity['actor']);
        if (isset($this->activity['object']))
            if (is_string($this->activity['object']))    $this->activity['object']=ActivityPub::GetObjectByUrl($this->user , $this->activity['object']);
        if (isset($this->activity['attributedTo']))
            if (is_string($this->activity['attributedTo']))    
                $this->activity['attributedTo']=ActivityPub::GetObjectByUrl($this->user , $this->activity['attributedTo']);
        if (isset($this->activity['type']))
        {
            if ($this->activity['type']=="Note")
            {
                if (!isset($this->activity['attributedTo']))
                    $this->activity['type']="Error 73843";
                // elimino etiquetas html de  $this->activity['content']
                $this->activity['content']=strip_tags($this->activity['content']);
                
            }
        }
    }

    public function render()
    {

            if ($this->activity['type']=="Note")
            {
                    Log::info(" esta es la nota: ".print_r($this->activity['attributedTo'],1));

                

                #Log::info('livewire activity: '. print_r($this->activity['attributedTo'],1));
            }
        

        #if ($this->activity['type']=="Note")            Log::print_r($this->activity['attributedTo']);
        

        return view('livewire.fediverso.activity', ['activity' => $this->activity]);
    }
}
