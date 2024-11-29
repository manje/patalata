<?php

namespace App\Traits;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

use App\Jobs\DistribuirFedi;

use App\Models\User;
use App\Models\Post;

use Illuminate\Support\Facades\Log;



trait ModelFedi
{
    /**
     * Boot the trait to listen for the model's creation event.
     */
    public static function bootModelFedi()
    {
        static::created(function ($model) {
            $model->distribute();
        });
    }

    public function GetActivity()
    {
        $idmodelo=Str::plural(Str::lower(class_basename($this)));
        $user = User::find($this->user_id);
        foreach ($this->APtranslate as $key => $value)
            $this->$key = $this->$value;
        if ($this->APtype=='Article')
        {
            $activity = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Article',
                'id' => Route($idmodelo.'.show', ['slug' => $this->slug]),
                'url' => Route($idmodelo.'.show', ['slug' => $this->slug]),
                'attributedTo' => Route('activitypub.actor', ['slug' => $user->slug]),
                'to' => 'https://www.w3.org/ns/activitystreams#Public',
                // 'cc' => Aquí necesitamos un endpoint para los seguidores de este usuario
                'published' => $this->created_at->toIso8601String(),
                'updated' => $this->updated_at->toIso8601String(),
                'summary' => $this->summary,
                'content' => $this->content,
                'mediaType' => 'text/html',
            ];
            if ($this->cover)
            {
                $activity['attachment'] = [
                    'type' => 'Image',
                    'mediaType' => 'image/jpeg',
                    'url' => asset('storage/'.$this->cover)
                ];
            }
            return $activity;
        }
        if ($this->APtype=='Note')
        {
            $activity = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'type' => 'Note',
                'id' => Route($idmodelo.'.show', ['slug' => $this->slug]),
                'url' => Route($idmodelo.'.show', ['slug' => $this->slug]),
                'attributedTo' => Route('activitypub.actor', ['slug' => $user->slug]),
                'to' => 'https://www.w3.org/ns/activitystreams#Public',
                // 'cc' => Aquí necesitamos un endpoint para los seguidores de este usuario
                'published' => $this->created_at->toIso8601String(),
                'updated' => $this->updated_at->toIso8601String(),
                'content' => $this->content,
                'mediaType' => 'text/html',
            ];
            if ($this->cover)
            {
                $activity['attachment'] = [
                    'type' => 'Image',
                    'mediaType' => 'image/jpeg',
                    'url' => asset('storage/'.$this->cover)
                ];
            }
            return $activity;
        }
        Log::info('Actividad sin tipo válido ' . $this->APtype);
        return false;
    }

    /**
     * Distribute the model to the queue.
     */
    public function distribute()
    {
        Queue::push(new DistribuirFedi($this));
    }



}
