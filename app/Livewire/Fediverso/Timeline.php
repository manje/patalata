<?php

namespace App\Livewire\Fediverso;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

use App\ActivityPub\ActivityPub;
use App\Models\Timeline as TL;

class Timeline extends Component
{
    public $timeline=null;
    private $user=null;
    public $actor=null;

    public function mount($actor=false)
    {

    }

    public function loadPosts()    
    {
        $this->user=auth()->user();
        if ($this->actor)
        {
            $outbox=ActivityPub::GetOutbox($this->user,$this->actor);
            if (count($outbox)>50) $list=array_slice($outbox,0,50);
            $this->timeline=$outbox;
            return true;
        }
        if ($this->user)
        {
            $list=TL::where('user_id',$this->user->id)->orderBy('created_at','desc')->take(50)->get();
            foreach ($list as $item)
            {
                $this->timeline[]=ActivityPub::GetObjectByUrl($this->user,$item->activity);
            }
        }
    }
    public function render()
    {
        return view('livewire.fediverso.timeline', ['timeline' => $this->timeline]);
    }
}
