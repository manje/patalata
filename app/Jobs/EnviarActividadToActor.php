<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\User;
use App\ActivityPub\ActivityPub;

use Illuminate\Support\Facades\Log;

class EnviarActividadToActor implements ShouldQueue
{
    use Queueable;

    public $user;
    public $actor;
    public $activity;
    
    public $tries=15;
    public $backoff = [30,60,180,300,3600,3600*4,3600*12,3600*12*24];

    /**
     * Create a new job instance.
     */
    public function __construct($user,$actor,$activity)
    {
        $this->user=$user;
        $this->actor=$actor;
        $this->activity=$activity;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {   
        $ap=new ActivityPub($this->user);
        $actor=$ap->GetActorByUrl($this->actor);
        if (isset($actor['error']))
        {
            if ($actor['error']=='Gone') return;
            Log::info($actor);
            Log::info("Error al enviar actividad a este actor: ".print_r($actor,1));
            throw new \Exception('Error al enviar actividad a '.$this->actor);
        }
        if ($actor===false) throw new \Exception('Error al localizar actor '.$this->actor);
        $json=json_encode($this->activity);
        
        
        $response=$ap->EnviarActividadPOST($json,$actor['inbox']);
        $responsetxt="$response";
        if (strlen($responsetxt)!=3)  throw new \Exception("Error $response al enviar actividad a ".$actor['inbox']);
        if (!in_array($response,[200,201,202]))
                throw new \Exception("Error $response al enviar actividad a ".$actor['inbox']);
    }
}
