<?php

namespace App\Livewire\Fediverso;

use Livewire\Component;
use Livewire\Attributes\On; 
use Illuminate\Support\Facades\Log;

use App\ActivityPub\ActivityPub;
use App\Models\Like;
use App\Models\Announce;
use App\Models\Reply;
use Carbon\Carbon;


class Activity extends Component
{
    
    public $activity;
    public $origen=false;
    public $loading=true;
    public $diferido=true;
    public $listrespuestas=[];
    public $respuestas=false;
    public $msgrespondiendo=true;
    public $like=false;
    public $rt=false;
    public $interactivo=true;
    protected $ap;
    protected $user;


    public function mount($activity,$diferido=true,$msgrespondiendo=true,$interactivo=true)
    {
        $this->ap();
        if ($msgrespondiendo==false) $this->msgrespondiendo=false;
        if (is_string($activity)) 
            $this->activity = $this->ap->GetObjectByUrl($activity);
        else
            $this->activity = $activity;
        $this->diferido=$diferido;
        $this->interactivo=$interactivo;
        if (!(isset($this->activity['sensitive']))) $this->activity['sensitive']=false;
        if (!$diferido) $this->cargar();
    }
    
    public function ap()    
    {
        $this->user = ActivityPub::GetIdentidad();
        $this->ap=new ActivityPub($this->user);
    }
    public function load()    
    {
        if ($this->diferido)
            $this->cargar();
    }
    public function cargar()    
    {
        $this->ap();
        $this->loading=false;
        if (isset($this->activity['error'])) $this->activity['type']="Error";
        if ($this->activity['type']=="Create")
        {
            if (is_string($this->activity['object']))    
                $this->activity=$this->ap->GetObjectByUrl($this->activity['object']);
            else
                $this->activity=$this->activity['object'];
        }
        if ($this->activity['type']=="Update")
        {
            if (is_string($this->activity['object']))    
                $this->activity=$this->ap->GetObjectByUrl($this->activity['object']);
            else
                $this->activity=$this->activity['object'];
        }
        if (isset($this->activity['error'])) $this->activity['type']="Error";
        if (isset($this->activity['errors'])) $this->activity['type']="Error";
        if (isset($this->activity['actor']))
            if (is_string($this->activity['actor']))
                $this->activity['actor']=$this->ap->GetActorByUrl($this->activity['actor']);
        if (isset($this->activity['published'])) $this->activity['published']=Carbon::parse($this->activity['published']);

        if (isset($this->activity['attributedTo']))
        {
            // hay que pensar como tratar esto, de tener más de un attributedTo
            #if (is_string($this->activity['object']))    $this->activity['object']=$this->ap->GetObjectByUrl($this->activity['object']);
            if (is_array($this->activity['attributedTo']))
            if (array_is_list($this->activity['attributedTo']))
                $this->activity['attributedTo']=$this->activity['attributedTo'][0];
            if (is_string($this->activity['attributedTo']))
            {
                $this->activity['attributedTo']=$this->ap->GetObjectByUrl($this->activity['attributedTo']);
                if (!(isset($this->activity['attributedTo']['preferredUsername']))) $this->activity['error']='Error en attributedTo';
            }
        }
        $this->activity['num_replies']='?';
        $this->activity['num_likes']='?';
        $this->activity['num_shares']='?';
        if (isset($this->activity['replies']))
        {
            $this->activity['num_replies']=(int)$this->ap->GetCountCollection($this->activity['replies']);
            $num=Reply::where('object', $this->activity['id'])->count();
            if ($num > 0) $this->activity['num_replies']=$num;
        }
        if (isset($this->activity['likes']))  $this->activity['num_likes']=(int)$this->ap->GetCountCollection($this->activity['likes']);
        if (isset($this->activity['shares']))  $this->activity['num_shares']=(int)$this->ap->GetCountCollection($this->activity['shares']);
        if (isset($this->activity['inReplyTo']))
        {
            if (is_string($this->activity['inReplyTo']))
                $this->activity['isreply']=$this->ap->GetObjectByUrl( $this->activity['inReplyTo']);
            else
                $this->activity['isreply']=$this->activity['inReplyTo'];
            if (isset($this->activity['isreply']['attributedTo']))
            {
                $this->activity['autororigen']=$this->ap->GetActorByUrl($this->activity['isreply']['attributedTo']);
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
        if (isset($this->activity['id']))
        {
            $this->like=(Like::where('object', $this->activity['id'])->where('actor', $this->user->GetActivity()['id'])->count()>0);
            $this->rt=(Announce::where('object', $this->activity['id'])->where('actor', $this->user->GetActivity()['id'])->count()>0);
        }
        if (isset($this->activity['content']))
            $this->activity['content']=ActivityPub::limpiarHtml($this->activity['content']);
        if (isset($this->activity['summary']))
            $this->activity['summary']=ActivityPub::limpiarHtml($this->activity['summary']);
        $this->activity['visible']='private';
        $to=[];
        if (isset($this->activity['to'])) $to=(array)$this->activity['to'];
        if (isset($this->activity['cc'])) $to=array_merge($to,(array)$this->activity['cc']);
        if (isset($this->activity['bto'])) $to=array_merge($to,(array)$this->activity['bto']);
        if (isset($this->activity['bcc'])) $to=array_merge($to,(array)$this->activity['bcc']);
        $this->activity['to']=array_unique($to);
        if (isset($this->activity['bto']))
            unset($this->activity['bto']);
        if (isset($this->activity['bcc']))
            unset($this->activity['bcc']);
        if (isset($this->activity['attributedTo']))
            if (isset($this->activity['attributedTo']['followers']))
                if (in_array($this->activity['attributedTo']['followers'], $this->activity['to']))
                    $this->activity['visible']='followers';
        if (in_array('https://www.w3.org/ns/activitystreams#Public', $this->activity['to']))
            $this->activity['visible']='public';
        if (isset($this->activity['type']))
        {
            if ($this->activity['type']=='Announce')
                $this->activity['visible']='public';
            $soportado=['Note','Page','Article','Event','Question','Audio','Video','Image','Announce'];
            if (!(in_array($this->activity['type'], $soportado)))
            {
                $this->activity['error']='Actividad no soportada '.$this->activity['type'];#.'<pre>'.print_r($this->activity,1).'</pre>';
            }
        }
        if ($this->activity['id']=='https://bsky.brid.gy/convert/ap/at://did:plc:3dp67jb5li3aiut32fvxxija/app.bsky.feed.post/3lxaej6xsoc2r')
        {
            Log::info(print_r($this->activity,1));
        }

    }


    public function verorigen()
    {
        $this->origen=true;
    }
    
    public function verrespuestas()
    {
        $this->ap();
        if ($this->respuestas)
            $this->respuestas=false;
        else
        {
            $this->listrespuestas=$this->ap->GetReplys($this->activity['id']);
            $this->respuestas=true;
        }
    }

    public function responder($id)
    {
        Log::info("responder a $id");
        $this->dispatch('responder_actividad',$id);
    }

    public function setlike()
    {
        $this->ap();
        // solo habíra que guardar el modelo y el envío se deberí hacer desde el trait 
        if ($this->like)
        {
            // undo
            $like=Like::where('object', $this->activity['id'])->where('actor', $this->user->GetActivity()['id'])->first();
            $activity=[
                '@context'=>'https://www.w3.org/ns/activitystreams',
                'id'=>$this->user->GetActivity()['id'].'#likes/'.$like->id,
                'type'=>'Undo',
                'actor'=>$this->user->GetActivity()['id'],
                'object'=>[
                    'id'=>$this->user->GetActivity()['id'].'#likes/'.$like->id,
                    'type'=>'Like',
                    'actor'=>$this->user->GetActivity()['id'],
                    'object'=>$this->activity['id']
                    ]
                ];
            $res=$this->ap->EnviarActividadPOST(json_encode($activity),$this->activity['attributedTo']['inbox']);
            Log::info('res '.print_r($res,1));
            $res="$res";
            if ($res[0]!='2')
            {
                Log::info('like3 error '.$res);
            }
            else
                $like->delete();
            $this->like=false;
        }
        else
        {
            $this->like=true;
            $like=Like::firstOrCreate(['object'=>$this->activity['id'], 'actor'=>$this->user->GetActivity()['id']]);
            $activity=[
                '@context'=>'https://www.w3.org/ns/activitystreams',
                'id'=>$this->user->GetActivity()['id'].'#likes/'.$like->id,
                'type'=>'Like',
                'actor'=>$this->user->GetActivity()['id'],
                'object'=>$this->activity['id']
            ];
            $res=$this->ap->EnviarActividadPOST(json_encode($activity),$this->activity['attributedTo']['inbox']);
            $res="$res";
            if ($res[0]!='2')
            {
                $this->like=false;
                $like->delete();

            }
        }
    }

    public function setimpulso()
    {
        $this->ap();
        // solo hay que guardar el modelo y el envío se hace desde el trait 
        Log::info("Hacemos rt1");
        if ($this->rt)
        {
            Announce::where('object', $this->activity['id'])->where('actor', $this->user->GetActivity()['id'])->first()->delete();
            $this->rt=false;
        }
        else
        {
            $rt=Announce::firstOrCreate(['object'=>$this->activity['id'], 'actor'=>$this->user->GetActivity()['id']]);
            $this->rt=true;
        }
    }

    public function render()
    {
        if (!(isset($this->activity['sensitive'])))
            Log::info(print_r($this->activity,1));
        if (!(isset($this->activity['type']))) return "<div>no type</div>";
        if ($this->activity['type']=='Accept') return "<div></div>";
        if ($this->activity['type']=='Note')
        if (isset($this->activity['attachments']))
            if (count($this->activity['attachments'])>0)
                Log::info(print_r($this->activity['attachments'],1));
        if (!(isset($this->activity['id']))) 
            Log::info(print_r($this->activity,1));
        if ((isset($this->activity['object']['error']))) return "<div>error</div>";
        return view('livewire.fediverso.activity');
    }
}
