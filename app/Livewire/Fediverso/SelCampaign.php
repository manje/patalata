<?php

namespace App\Livewire\Fediverso;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

use App\ActivityPub\ActivityPub;

use App\Models\Apfollowing;
class SelCampaign extends Component
{
    public $user;
    public $busqueda=[];
    public $listado=[];
    public $campaigns=[];
    public $search = '';
    protected $ap;

    public function mount($campaigns=false)
    {
        $this->user=auth()->user();
        if ($campaigns)
            $this->listado=$campaigns;
        $this->ap=new ActivityPub($this->user);
    }

    public function add($id)
    {
        $actor=$this->ap->GetActorByUrl($id);
        $this->listado[$id]=$actor;
        $this->campaigns=[];
        foreach ($this->listado as $a)
            $this->campaigns[]=$a['id'];
        $this->busqueda=[];
        $this->search='';

    }
    public function del($id)
    {
        unset($this->listado[$id]);
        foreach ($this->listado as $a)
            $this->campaigns[]=$a['id'];

    }

    public function updatedSearch()
    {
        $this->busqueda=[];
        if (strlen($this->search)<3) return;
        $completo=$this->ap->GetActorByUsername($this->search);
        if (isset($completo['campaign']))
            if ($completo['campaign'])
            { 
                $this->busqueda=[$completo];
            }
        $perfiles=[];
        $teams=$this->user->allTeams();
        foreach ($teams as $team)
        {
            $perfiles[]=$team->GetActivity()['id'];
        }
        $perfiles[]=$this->user->GetActivity()['id'];
        $target=[];
        foreach ($perfiles as $id)
        {
            $res=Apfollowing::where('actor',$id)->get();
            foreach ($res as $f)
                $target[]=$f->object;
        }
        $target=array_unique($target);
        foreach ($target as $t)
        {
            if ($completo)
            if ($t==$completo['id']) continue;
            $a=$this->ap->GetActorByUrl($t);
            if (isset($a['userfediverso']))
                if (isset($a['campaign']))
                    if ($a['campaign'])
                    {
                        if (!(strpos(strtolower($a['userfediverso']),strtolower($this->search))===false)) 
                            $this->busqueda[]=$a;
                        else
                            if (!(strpos(strtolower($a['name']),strtolower($this->search))===false)) $this->busqueda[]=$a;
                    }
            if (count($this->busqueda)>=20) break;             
        }



    }
    public function render()
    {
        return view('livewire.fediverso.sel-campaign');
    }
}
