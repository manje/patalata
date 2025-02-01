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
        Log::info("EnviarActividadToActor ".$this->actor);
        $actor_to=ActivityPub::GetActorByUrl($this->user,$this->actor);
        if ($actor===false) throw new \Exception('Error al localizar actor '.$this->actor);
        $json=json_encode($this->activity);
        $response=ActivityPub::EnviarActividadPOST($this->user,$json,$actor['inbox']);
        $responsetxt="$response";
        if (strlen($responsetxt)!=3)  throw new \Exception("Error $response al enviar actividad a ".$actor['inbox']);
        if (!in_array($response,[200,201,202]))
                throw new \Exception("Error $response al enviar actividad a ".$actor['inbox']);
    }
}
