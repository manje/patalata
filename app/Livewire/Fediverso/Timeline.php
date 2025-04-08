<?php

namespace App\Livewire\Fediverso;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

use App\ActivityPub\ActivityPub;
use App\Models\Timeline as TL;

class Timeline extends Component
{
    public $timeline=false;
    public $user=null;
    public $actor=null;
    public $nuevas=0;
    public $nuevaslist=[];
    public $primero=false;
    public $serial=0;
    public $siguienteprimero=false;
    public $numactividades=20;
    public $ultimo=0;

    protected $listeners = ['loadMore'];

    public function mount($actor=false)
    {
        if ($actor)
        {
            $this->actor=$actor;
            return;
        }
        $this->user=ActivityPub::GetIdentidad();
    }

    public function loadMore()
    {
        if ($this->user)
        {
            $list=TL::where('user',$this->user->GetActivity()['id'])->where('id','<', $this->ultimo)->orderBy('id', 'desc')->take(100)->get();
            foreach ($list as $item)
            {
                $a=ActivityPub::GetObjectByUrl($this->user,$item->activity);
                if (isset($a['id']))
                {
                    $this->timeline[]=['id'=>$a['id'],'serial'=>$this->serial,'act'=>$a];
                }
            }
            if (count($list)>0) $this->ultimo=$item->id;
        }
    }

    public function VerNuevas()
    {
        if (count($this->nuevaslist)>0)
        {
            $this->timeline=$this->nuevaslist+$this->timeline;
            $max=$this->nuevas+20;
            if (count($this->timeline)>$max) $this->timeline=array_slice($this->timeline,0,$max);
            $this->nuevaslist=[];
            $this->nuevas=0;
            $this->serial++;
            $this->primero=$this->siguienteprimero;
        }
    }

    public function Nuevas()
    {
        if ($this->actor)
        {
            $this->nuevas=0;

        }
        if ($this->user)
        if ($this->primero)
        {
            # nº nuevas, count
            $list=TL::where('user',$this->user->GetActivity()['id'])->where('id','>', $this->primero->id)->count();
            if ($list>0)
            {
                $this->siguienteprimero=false;
                $this->nuevas=$list;
                $list=TL::where('user',$this->user->GetActivity()['id'])->where('id','>', $this->primero->id)->orderBy('id', 'desc')->take($list)->get();
                $this->nuevaslist=[];
                foreach ($list as $item)
                {
                    if ($this->siguienteprimero===false) $this->siguienteprimero=$item;
                    $a=ActivityPub::GetObjectByUrl($this->user,$item->activity);
                    if (isset($a['id']))
                    {
                        $this->nuevaslist[]=['id'=>$a['id'],'serial'=>$this->serial,'act'=>$a];
                    }
                    $this->nuevas=count($this->nuevaslist);
                }
            }
        }
    }

    public function loadPosts()    
    {
        if ($this->actor)
        {
            $u=ActivityPub::GetIdentidad();
            $outbox=(array)ActivityPub::GetColeccion($u,$this->actor['outbox'],false,5);
            #if (count($outbox)>50) $list=array_slice($outbox,0,50);
            $this->timeline=[];
            foreach ($outbox as $k=>$v)
            {
                if (is_string($v)) 
                    $a=ActivityPub::GetObjectByUrl($u,$v);
                else
                    $a=$v;
                if (isset($a['id']))
                    $this->timeline[]=['id'=>$a['id'],'serial'=>$this->serial,'act'=>$a];
            }
        }
        if ($this->user)
        {
            $this->primero=false;
            $list=TL::where('user',$this->user->GetActivity()['id'])->orderBy('id','desc')->take($this->numactividades)->get();
            $this->timeline=[];
            foreach ($list as $item)
            {
                if ($this->primero===false) $this->primero=$item;
                $a=ActivityPub::GetObjectByUrl($this->user,$item->activity);
                if (isset($a['id']))
                {
                    $this->timeline[]=['id'=>$a['id'],'serial'=>$this->serial,'act'=>$a];
                }
            }
            if (isset($item))
                $this->ultimo=$item->id;
        }
        $this->serial++;
    }
    public function render()
    {
        return view('livewire.fediverso.timeline', ['timeline' => $this->timeline,'nuevas' => $this->nuevas,'serial' => $this->serial]);
    }
}
