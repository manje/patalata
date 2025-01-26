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


class DistribuirFedi implements ShouldQueue
{
    use Queueable;

    public $data;
    public $user;
    public $activity;

    /**
     * Create a new job instance.
     */
    public function __construct($data,$user,$activity=false)
    {
        $this->data = $data;
        $this->user = $user;
        $this->activity = $activity;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('DistribuirFedi: ');
        $followers=Apfollower::where('actor', $this->user->GetActivity()['id'])->get();
        $list=[];
        foreach ($followers as $follower)
        {
            $b=Block::where('actor', $this->user->GetActivity()['id'])->where('object', $follower->object)->first();
            if (!$b) $b=Block::where('actor', $follower->object)->where('object', $this->user->GetActivity()['id'])->first();
            if ($b) continue;
            $list[]=$follower->object;

            $data=['modelo'=> $this->data, 'actor'=> $this->user->GetActivity()['id'], 'follower' => $follower->object , 'user' => $this->user];
            EnviarFedi::dispatch($data,$this->activity);
        }
        Log::info(count($followers).' envidados.');    
        if ($this->data)
        if ($this->data->APtype=='Announcement') 
        {
            $objeto=ActivityPub::GetObjectByUrl($this->user,$this->data->object);
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
                    $objeto=ActivityPub::GetObjectByUrl($this->user,$this->activity['object']['object']);
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
