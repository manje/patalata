<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\User;
use App\ActivityPub\HTTPSignature;
use App\ActivityPub\ActivityPub;

use Illuminate\Support\Facades\Log;

class EnviarFedi implements ShouldQueue
{
    use Queueable;

    public $backoff=300;

    public $data;
    public $activity;

    /**
     * Este trabajo se debe dejar de usar a favor de EnviarActividadToActor
     */
    public function __construct($data,$activity=false)
    {
        $this->data = $data;
        $this->activity=$activity;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {   
        if ($this->activity)
            $json=$this->activity;
        else
        {
            $activity=$this->data['modelo']->GetActivity();
            Log::info("Enviar Fedi $activity[id]");
            $json = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $activity['id'],
            'type' => 'Create',
            'actor' => $this->data['actor'],
            'to' => ['https://www.w3.org/ns/activitystreams#Public'],
            'object' => $activity
            ];
        }
        $json=json_encode($json);
        $actor=ActivityPub::GetActorByUrl($this->data['user'],$this->data['follower']);
        $headers = HTTPSignature::sign($this->data['user'], $json, $actor['inbox']);
        $ch = curl_init($actor['inbox']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        $codigo=curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res=curl_getinfo($ch);
        $response=json_decode($response, true); 
        $headers=curl_getinfo($ch, CURLINFO_HEADER_OUT);
        Log::info('enviar actividad a '.$this->data['follower']);
        Log::info('Inbox response: '.$codigo);
        if (isset($response->error))
                throw new \Exception('Error al distribuir la fedi');
        if (!in_array($codigo,[200,201,202]))
                throw new \Exception("Error al distribuir la fedi, código $codigo, respuesta: ".print_r($headers,1));
    
    }


    /*
    public function retryUntil(): DateTime
    {
        return now()->addDays(2);
    }
    */

}
