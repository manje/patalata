<?php

namespace App\Traits;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

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
            if ($this->APtype!='Person')
                $model->distribute();
        });
    }

    public function GetActivity()
    {
        if (isset($this->APtranslate))
            foreach ($this->APtranslate as $key => $value)
                $this->$key = $this->$value;
        if ($this->APtype=='Person')
        {
            $activity = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'id' => route('activitypub.actor', ['slug' => $this->slug]),
                'type' => 'Person',
                'preferredUsername' => $this->slug,
                'name' => $this->name,
                'following' => route('activitypub.following', ['slug' => $this->slug]),
                'inbox' => route('activitypub.inbox', ['slug' => $this->slug]),
                'outbox' => route('activitypub.outbox', ['slug' => $this->slug]),            
                'publicKey' => [
                    'id' => route('activitypub.actor', ['slug' => $this->slug]) . '#main-key',
                    'owner' => route('activitypub.actor', ['slug' => $this->slug]),
                    'publicKeyPem' => $this->public_key,
                ],
                'icon' => [
                    'type' => 'Image',
                    'mediaType' => 'image/png',
                    'url' => $this->profile_photo_url,
                ],
            ];
            return $activity;
        }
    



        if ($this->APtype=='Article')
        {
            $idmodelo=Str::plural(Str::lower(class_basename($this)));
            $user = User::find($this->user_id);
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
            $idmodelo=Str::plural(Str::lower(class_basename($this)));
            $user = User::find($this->user_id);    
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
                $activity['attachment'] = [];
                $activity['attachment'][] = [
                    'type' => 'Image',
                    'mediaType' => 'image/jpeg',
                    'url' => asset('storage/'.$this->cover)
                ];
            }
            return $activity;
        }
        return false;
    }

    /**
     * Distribute the model to the queue.
     */
    public function distribute()
    {
        Queue::push(new DistribuirFedi($this));
    }

    public function Collection($listado,$url): JsonResponse
    {
        // Compruebo si hay parámetro page
        if (!(Request::has('page'))) {
            $total=$listado->count();
            $list = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'id' => $url,
                'type' => 'OrderedCollection',
                'totalItems' => $total,
                'first' => $url.'?page=1',
            ];
            return response()->json($list, 200, ['Content-Type' => 'application/activity+json']);
        }
        // Recuperar seguidores paginados
        $res = $listado->orderBy('id','desc')
            ->paginate(3); // Cambia el número según tus necesidades (ej. 20 seguidores por página)

        $list = [];
        foreach ($res as $item) {
            $list[] = $item->object;
        }

        // Paginated response
        $col = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $url,
            'type' => 'Collection',
            'totalItems' => $res->total(),
            'orderedItems' => $list,
            'last' => $res->url($res->lastPage()), // URL para la última página
        ];
        if ($res->hasMorePages())
            $col['next'] = $res->nextPageUrl(); // URL para la página siguiente
        if (!$res->onFirstPage())
            $col['prev'] = $res->previousPageUrl(); // URL para la página anterior
        return response()->json($col, 200, ['Content-Type' => 'application/activity+json']);
    }


}
