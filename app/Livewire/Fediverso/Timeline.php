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
    public $nuevaslist=null;
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
        Log::info('en mount del tiemline la identidad es '.$this->user->slug);
    }

    public function loadMore()
    {
        Log::info("load more");
        #if ($this->actor) return true;
        if ($this->user)
        {
            $list=TL::where('user',$this->user->GetActivity()['id'])->where('id','<', $this->ultimo)->orderBy('id', 'desc')->take(100)->get();
            $co=0;
            foreach ($list as $item)
            {
                $a=ActivityPub::GetObjectByUrl($this->user,$item->activity);
                if (isset($a['id']))
                {
                    $idtl=$item->id;
                    $this->timeline[$idtl]=$a;
                    if ($co++ > 10) break;
                }
            }
            Log::info("nuevo ultimo es ".$item->id);
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
                        $idtl=$item->id;
                        $this->nuevaslist[$idtl]=$a;
                    }
                }
            }
        }
    }

    public function loadPosts()    
    {
        if ($this->actor)
        {
            $u=ActivityPub::GetIdentidad();
            Log::info('timeline loadposts actor '.$this->actor['outbox'].' y usuario '.$u->slug);
            $outbox=ActivityPub::GetColeccion($u,$this->actor['outbox'],false,5);
            Log::info(print_r($outbox,1));
            #if (count($outbox)>50) $list=array_slice($outbox,0,50);
            $this->timeline=$outbox;
            Log::info('timeline loadposts actor'.print_R($outbox,true));
            foreach ($this->timeline as $k=>$v)
            {
                if (is_string($v)) $this->timeline[$k]=ActivityPub::GetObjectByUrl($u,$v);
            }
            #$this->timeline=[];
        }
        if ($this->user)
        {
            Log::info('timeline loadposts user');
            $this->primero=false;
            $list=TL::where('user',$this->user->GetActivity()['id'])->orderBy('id','desc')->take($this->numactividades)->get();
            Log::info(count($list).' actividades');
            if (count($list)==0)
                $this->timeline=[];
            else
                foreach ($list as $item)
                {
                    if ($this->primero===false) $this->primero=$item;
                    $a=ActivityPub::GetObjectByUrl($this->user,$item->activity);
                    if (isset($a['id']))
                    {
                        $idtl=$item->id;
                        $this->timeline[$idtl]=$a;
                    }
                }
            if (isset($item))
                $this->ultimo=$item->id;
        }
    }
    public function render()
    {
        return view('livewire.fediverso.timeline', ['timeline' => $this->timeline,'nuevas' => $this->nuevas,'serial' => $this->serial]);
    }
}
