<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use App\Models\Post;
use App\Models\Apfollower;

use Illuminate\Support\Facades\Log;


class DistribuirFedi implements ShouldQueue
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
        Log::info('DistribuirFedi: '.$this->data->id);
        $user=User::find($this->data['user_id']);
        // busco sus followers
        $followers=Apfollower::where('actor', $user->GetActivity()['id'])->get();
        // recorro los followers y creo por cada uno un trabajo para enviarlo
        foreach ($followers as $follower)
        {
            $data=['modelo'=> $this->data, 'actor'=> $user->GetActivity()['id'], 'follower' => $follower]->object;
            EnviarFedi::dispatch($data);
        }
        Log::info(count($followers).' envidados.');
    
    }
}
