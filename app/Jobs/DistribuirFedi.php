<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use App\Models\Post;
use App\Models\Apfollower;
use App\Models\Block;

use App\ActivityPub\ActivityPub;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Queue;

/* Envía una actividad a todos los followeres */

class DistribuirFedi implements ShouldQueue
{
    use Queueable;

    public $data;
    public $user;
    public $activity;
    public $ap;

    /**
     * Create a new job instance.
     */
    public function __construct($data,$user,$activity=false)
    {
        $this->data = $data;  // evitemos $data y usemos activity
        $this->user = $user;
        $this->activity = $activity;
        $this->ap=new ActivityPub($user);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('DistribuirFedi:  hadle');
        if (($this->activity['type']=='Create') || ($this->activity['type']=='Update'))
        {
            $this->activity['object']=$this->ap->GetObjectByUrl($this->activity['object']['id']);
        }
        Log::info('DistribuirFedi: inicio ');
        $followers=Apfollower::where('actor', $this->user->GetActivity()['id'])->get();
        $list=[];
        foreach ($followers as $follower)
        {
            $b=Block::where('actor', $this->user->GetActivity()['id'])->where('object', $follower->object)->first();
            if (!$b) $b=Block::where('actor', $follower->object)->where('object', $this->user->GetActivity()['id'])->first();
            if ($b) continue;
            $list[]=$follower->object;
            Log::info("fffffffffffffff ".$follower->object);
            $data=['modelo'=> $this->data, 'actor'=> $this->user->GetActivity()['id'], 'follower' => $follower->object , 'user' => $this->user];
            #EnviarFedi::dispatch($data,$this->activity);
            Queue::push(new EnviarActividadToActor($this->user,$follower->object,$this->activity));
        }
        Log::info(count($followers).' envidados.');    
        
        if (isset($this->activity['inReplyTo']))
        {
            try {
                $obj=$this->ap->GetObjectByUrl($this->activity['inReplyTo']);
                $b=Block::where('actor', $this->user->GetActivity()['id'])->where('object', $obj['attributedTo'])->first();
                if (!$b) $b=Block::where('actor', $obj['attributedTo'])->where('object', $this->user->GetActivity()['id'])->first();
                if ($b) return;
                if (!in_array($obj['attributedTo'],$list))
                    Queue::push(new EnviarActividadToActor($this->user,$obj['attributedTo'],$this->activity));
            } catch (\Throwable $th) {
                Log::error($th);
            }
        }
        if ($this->data)
        if ($this->data->APtype=='Announcement') 
        {
            $objeto=$this->ap->GetObjectByUrl($this->data->object);
            $usuario=$objeto['attributedTo'];
            $b=Block::where('actor', $this->user->GetActivity()['id'])->where('object', $usuario)->first();
            if (!$b) $b=Block::where('actor', $usuario)->where('object', $this->user->GetActivity()['id'])->first();
            if ($b) return;
            if (!in_array($usuario,$list))
            {
                $data=['modelo'=> $this->data, 'actor'=> $this->user->GetActivity()['id'], 'follower' => $usuario , 'user' => $this->user];
                EnviarFedi::dispatch($data,$this->activity);
                Log::info('Envidados también al autor.');    
            }
            

        }
        if ($this->activity)
        {
            if ($this->activity['type']=='Undo')
            {
                // Los Undo los envio aunque esté el destinatario bloqueado
                if ($this->activity['object']['type']=='Announce')
                {
                    $objeto=$this->ap->GetObjectByUrl($this->user,$this->activity['object']['object']);
                    $usuario=$objeto['attributedTo'];
                    if (!in_array($usuario,$list))
                    {
                        $data=['modelo'=> $this->data, 'actor'=> $this->user->GetActivity()['id'], 'follower' => $usuario , 'user' => $this->user];
                        EnviarFedi::dispatch($data,$this->activity);
                        Log::info('Envidados también al autor.');    
                    }
                }
            }
        }
    }
}
