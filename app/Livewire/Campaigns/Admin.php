<?php

namespace App\Livewire\Campaigns;

use Livewire\Component;

use App\ActivityPub\ActivityPub;
use App\Models\Member;

class Admin extends Component
{
    public $campaign;
    public $busca;
    public $invitado=null;

    public function mount($campaign)
    {
        $this->campaign=$campaign;
        $this->ap=new ActivityPub(ActivityPub::GetIdentidad());
    }

    public function updatedBusca()
    {
        $this->invitado=ActivityPub::GetActorByUsername($this->busca);
    }

    public function PrevenirAdmin($actor,$rol)
    {
        if ($rol=='admin') return true;
        $num=Member::where('actor',$this->campaign['id'])->where('status','admin')->count();
        if ($num>1) return true;
        $mem=Member::where('actor',$this->campaign['id'])->where('object',$actor)->first();
        if ($mem->status=='admin')
            return false;
        else
            return true;
    }

    public function Invitar($actor)
    {
        if (Member::where('actor',$this->campaign['id'])->where('object',$this->invitado['id'])->count()==0)
            Member::create([
                'actor'=>$this->campaign['id'],
                'object'=>$this->invitado['id'],
                'status'=>'Invite'
            ]);
        $this->invitado=false;        
        $this->busca='';        
    }

    public function SetRol($actor,$rol)
    {
        \Illuminate\Support\Facades\Log::info("setrol $actor - $rol");
        if ($this->PrevenirAdmin($actor,$rol))
            Member::where('actor',$this->campaign['id'])->where('object',$actor)->update(['status'=>$rol]);
    }

    public function BorrarMiembro($actor)
    {
        \Illuminate\Support\Facades\Log::info("borrar $actor");
        if ($this->PrevenirAdmin($actor,'delete'))
            Member::where('actor',$this->campaign['id'])->where('object',$actor)->delete();
    }

    
    public function render()
    {
        $miembros=Member::where('actor',$this->campaign['id'])->orderByDesc('id')->get();
        $list=['admin'=>[],'editor'=>[],'Join'=>[],'Invite'=>[]];
        foreach ($miembros as $m)
        {
            $k=$m->status;
            $c=$m->created_at;
            
            $ap=new ActivityPub( Auth()->user() );
            $m=$ap->GetActorByUrl( Auth()->user());
            if ($m)
            {
                $m['created_at']=$c;
                $list[$k][]=$m;
            }
        }
        return view('livewire.campaigns.admin',[
            'campaign'=>$this->campaign,
            'list'=>$list,
            'invitado'=>$this->invitado,
        ]);
    }
}
