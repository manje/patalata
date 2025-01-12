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


    public $data;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {   
        $activity=$this->data['modelo']->GetActivity();
        Log::info('actor origen: '.$this->data['actor']);
        $json = [
          '@context' => 'https://www.w3.org/ns/activitystreams',
          'id' => $activity['id'],
          'type' => 'Create',
          'actor' =>  route('activitypub.actor', ['slug' => $user->slug]),
          'to' => ['https://www.w3.org/ns/activitystreams#Public'],
          'object' => $activity
        ];
        $json=json_encode($json);
        Log::info('json: '.$json);
        Log::info('inbox: '.$this->data['follower']);
        $actor=ActivityPub::GetActorByUrl($user,$this->data['follower']);
        Log::info('actor inbox: '.print_R($actor['inbox'],1));
        $headers = HTTPSignature::sign($user, $json, $actor['inbox']);
        $ch = curl_init($actor['inbox']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        $codigo=curl_getinfo($ch, CURLINFO_HTTP_CODE);
        Log::info('Inbox response: '.$codigo);
        $response=json_decode($response, true); 
        if (isset($response->error))
                throw new \Exception('Error al distribuir la fedi');
        if ($codigo!=202)
                throw new \Exception('Error al distribuir la fedi, código $codigo, respuesta: '.print_r($response,1));
    
    }
}
