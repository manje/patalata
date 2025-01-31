<?php

namespace App\Traits;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Jobs\DistribuirFedi;
use App\Jobs\EnviarActividadToActor;

use App\Models\User;
use App\Models\Team;
use App\Models\Post;
use App\Models\Outbox;

use Illuminate\Support\Facades\Log;

trait ModelFedi
{
    /**
     * Boot the trait to listen for the model's creation event.
     */
    public static function bootModelFedi()
    {
        static::created(function ($model) {
            Log::info("tipo: ".$model->APtype." act ".$model->actor);
            if ($model->APtype!='Person')
            {
                Log::info("slug - id ");
                if (!$model->slug) $model->slug=$model->id;
                $model->distribute();
                // creo objeto Outbox
                if (isset($model->actor))
                    $actor=$model->actor;
                else
                    $actor=$model->GetActivity()['attributedTo'];
                Outbox::create([
                    'actor' => $actor,
                    'object' => $model->GetActivity()['id']
                ]);
            }
        });

        static::deleting(function ($model) {
           
            Log::info('Se está eliminando el modelo con ID: ' . $model->id);
            if ($model->APtype=='Announce')
            {
                $activity=[
                    '@context' => 'https://www.w3.org/ns/activitystreams',
                    'id' => $model->GetActivity()['id'],
                    '@context' => 'https://www.w3.org/ns/activitystreams',
                    'id' => $model->GetActivity()['id'].'#undo',
                    'type' => 'Undo',
                    'actor' => $model->GetActivity()['actor'],
                    'object' => $model->GetActivity()
                ];
                $model->distribute($activity);
            }
            // Si necesitas detener la eliminación, puedes lanzar una excepción
            // throw new \Exception("No se puede eliminar este modelo");
            // hay que lanzar delete si se trata de un objeto que se ha distribuido con create

        });    
    }

    public function GetActor()
    {
        if (isset($this->actor))
            return $this->actor;
        else
        {
            $idmodelo=Str::plural(class_basename($this));
            $user=User::find($this->user_id);
            $actor=$user->GetActivity()['id'];
        }
    }

    public function GetActivity()
    {
        if (isset($this->APtranslate))
            foreach ($this->APtranslate as $key => $value)
                $this->$key = $this->$value;
        $activity=false;
        if ($this->APtype=='Person')
        {
            $activity = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'id' => route('activitypub.actor', ['slug' => $this->slug]),
                'url' => route('fediverso.profile', ['slug' => $this->slug]),
                'type' => 'Person',
                'preferredUsername' => $this->slug,
                'published' => $this->created_at->toIso8601String(),
                'name' => $this->name,
                'following' => route('activitypub.following', ['slug' => $this->slug]),
                'followers' => route('activitypub.followers', ['slug' => $this->slug]),
                'inbox' => route('activitypub.inbox', ['slug' => $this->slug]),
                'outbox' => route('activitypub.outbox', ['slug' => $this->slug]),            
                'publicKey' => [
                    'id' => route('activitypub.actor', ['slug' => $this->slug]) . '#main-key',
                    'owner' => route('activitypub.actor', ['slug' => $this->slug]),
                    'publicKeyPem' => $this->public_key,
                ]
        
            ];
            if ($this->profile_photo_url)
            {
                $activity['icon'] = [
                    'type' => 'Image',
                    'mediaType' => 'image/png',
                    'url' => $this->profile_photo_url,
                ];
            }
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
        }

        if ($this->APtype=='Announce')
        {
            $idmodelo=Str::plural(Str::lower(class_basename($this)));
            $activity = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'id' => Route($idmodelo.'.show', ['slug' => $this->id]),
                'type' => 'Announce',
                'actor' => $this->actor,
                'published' => $this->created_at->toIso8601String(),
                'to' => 'https://www.w3.org/ns/activitystreams#Public',
                // 'cc' => Aquí necesitamos un endpoint para los seguidores de este usuario
                'object' => $this->object
            ];
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
        }
        if ($this->APtype=='Block')
        {
            $activity = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'id' => $this->actor."#/block/".$this->id,
                'type' => 'Note',
                'actor' => $this->actor,
                'object' => $this->object

            ];
        }
        return $activity;
    }

    public function distribute($activity=false)
    {
        if (($this->APtype=='Announce') || ($this->APtype=='Block') )
        {
            Log::info('comprobamos si la actividad está creada por nuestra instancia');
            // Solo distribuimos los impulsos que sean creados en local
            if  ( parse_url(config('app.url'), PHP_URL_HOST) != parse_url($this->actor, PHP_URL_HOST))
                return false;
            Log::info('distribuye '.$this->APtype.' porque no es recibido');



            $slug=explode('/',$this->actor);
            $slug=$slug[count($slug)-1];
            $user=$this->SlugToUser($slug);
            Log::info("El usuario tiene id ".$user->id);
            if ($this->APtype=='Block')
            {
                // los bloqueos no se distribuyen a los seguidores
                Queue::push(new EnviarActividadToActor($user,$this->object,$this->GetActivity()));
            }

            
        }
        else
        {
            if ($this->team_id)
              $user=Team::find($this->team_id);
            else
              $user=User::find($this->user_id);
        }
        Log::info('envio dist');
        if ($activity)
            $mod=false;
        else
            $mod=$this;
        if ($mod===false)
            Log::info("se envia por activity");
        else
            Log::info("se envia por model");
        Queue::push(new DistribuirFedi($mod,$user,$activity));
    }

    public function SlugToUser($slug)
    {
        $user=User::where('slug', $slug)->first();
        if (!($user)) $user=Team::where('slug', $slug)->first();
        if (!($user)) return false;
        return $user;
    }

    public function Collection($listado,$url): JsonResponse
    {
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
        $res = $listado->orderBy('id','desc')
            ->paginate(20);
        $list = [];
        foreach ($res as $item) $list[] = $item->object;
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
