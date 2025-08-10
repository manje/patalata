<?php

namespace App\Traits;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Jobs\DistribuirFedi;
use App\Jobs\EnviarActividadToActor;

use App\Models\User;
use App\Models\Team;
use App\Models\Post;
use App\Models\Outbox;
use App\Models\Apfile;
use App\Models\Like;
use App\Models\Announce;

use App\ActivityPub\ActivityPub;

use Illuminate\Support\Facades\Log;

use League\CommonMark\CommonMarkConverter;

trait ModelFedi
{
    /**
     * Boot the trait to listen for the model's creation event.
     */
    public static function bootModelFedi()
    {
        static::created(function ($model) {
            Log::info("tipo: ".$model->APtype." act ".$model->actor);

            $validos=['Note','Article','Announce','Question','Page'];
            if (in_array($model->APtype,$validos))
            {
                Log::info("slug - id ");
                if (!$model->slug) $model->slug=$model->id;
                #$model->distribute();

                $json = [
                    '@context' => 'https://www.w3.org/ns/activitystreams',
                    'id' => $model->GetActivity()['id'],
                    'type' => 'Create',
                    'actor' => $model->GetActor(),
                    'to' => ['https://www.w3.org/ns/activitystreams#Public'],
                    'object' => $model->GetActivity()
                    ];
                $user=$model->User();
                // creo objeto Outbox
                if (isset($model->actor))
                    $actor=$model->actor;
                else
                    $actor=$model->GetActivity()['attributedTo'];
                Outbox::create([
                    'actor' => $actor,
                    'object' => $model->GetActivity()['id']
                ]);
                Queue::later(now()->addSeconds(5), new DistribuirFedi(false, $user, $json));
                #Queue::push(new DistribuirFedi(false,$user,$json));
            }
            if ($model->APtype=='Collection')
            {
                $idmodelo=Str::plural(class_basename($model));
                Log::info("modelo: $idmodelo");
                if ($idmodelo=='Members')
                {
                    if ($model->status=='Join')
                    {
                        if  ( parse_url(config('app.url'), PHP_URL_HOST) != parse_url($model->object, PHP_URL_HOST))
                        {
                            $activity=[
                                '@context' => 'https://www.w3.org/ns/activitystreams',
                                'type' => 'Join ',
                                'actor' => $model->object, // equipo
                                'object' => $model->actor, // campaña
                                'to' => [$model->object]
                            ];
                            $user=explode("/", $model->object);
                            $user=ActivityPub::GetIdentidadBySlug(array_pop($user));
                            Queue::push(new EnviarActividadToActor($user,$this->actor,$activity));


                        }
                    }
                    if ($model->status=='Invite')
                    {
                        Log::info('se crea un Invite');
                        if  ( parse_url(config('app.url'), PHP_URL_HOST) != parse_url($model->object, PHP_URL_HOST))
                        {
                            $activity=[
                                '@context' => 'https://www.w3.org/ns/activitystreams',
                                'type' => 'Invite',
                                'actor' => $model->actor, // campaña
                                'object' => $model->object, // equipo
                                'to' => [$model->object]
                            ];
                            $user=explode("/", $model->actor);
                            $user=ActivityPub::GetIdentidadBySlug(array_pop($user));
                            Queue::push(new EnviarActividadToActor($user,$model->object,$activity));
                        }
                    }

                }

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
            // hay que lanzar delete si se trata de un objeto que se ha distribuido con create
            $validos=['Note','Article','Announce','Question','Page'];
            if (in_array($model->APtype,$validos))
            {
                $activity=[
                    '@context' => 'https://www.w3.org/ns/activitystreams',
                    'id' => $model->GetActivity()['id'].'#delete',
                    'type' => 'Delete',
                    'actor' => $model->GetActivity()['actor'],
                    'object' => $model->GetActivity() // comprobar si los deletes se hacen así o mandando solo el id
                ];
                $model->distribute($activity);
            }

            if ($model->APtype=='Collection')
            {
                $idmodelo=Str::plural(class_basename($model));
                Log::info("modelo: $idmodelo");
                if ($idmodelo=='Members')
                {
                    if ($model->status=='Invite')
                    {
                        if ( parse_url(config('app.url'), PHP_URL_HOST) != parse_url($model->actor, PHP_URL_HOST))
                        {
                            $activity=[
                                '@context' => 'https://www.w3.org/ns/activitystreams',
                                'type' => 'Reject',
                                'actor' => $model->object, // equipo
                                'object' => [
                                    'type' => 'Invite',
                                    'actor' => $model->actor, // campaña
                                    'object' => $model->object, // equipo
                                ],
                                'to' => [$model->actor]
                            ];
                            $user=explode("/", $model->object);
                            $user=ActivityPub::GetIdentidadBySlug(array_pop($user));
                            Queue::push(new EnviarActividadToActor($user,$this->actor,$activity));
                        }
                    }
                    if ($model->status=='Join')
                    {
                        if ( parse_url(config('app.url'), PHP_URL_HOST) != parse_url($model->object, PHP_URL_HOST))
                        {
                            $activity=[
                                '@context' => 'https://www.w3.org/ns/activitystreams',
                                'type' => 'Reject',
                                'actor' => $model->actor, // campaña
                                'object' => [
                                    'type' => 'Join',
                                    'actor' => $model->object, // equipo
                                    'object' => $model->actor, // campaña
                                ],
                                'to' => [$model->object]
                            ];
                            $user=explode("/", $model->actor);
                            $user=ActivityPub::GetIdentidadBySlug(array_pop($user));
                            Queue::push(new EnviarActividadToActor($user,$this->object,$activity));
                        }
                    }
                }
            }
        });

        static::updating(function ($model) 
        {
            $validos=['Note','Article','Announce','Question','Page'];
            if (in_array($model->APtype,$validos))
            {
                $json = [
                    '@context' => 'https://www.w3.org/ns/activitystreams',
                    'id' => $model->GetActivity()['id'],
                    'type' => 'Update',
                    'actor' => $model->GetActor(),
                    'to' => ['https://www.w3.org/ns/activitystreams#Public'],
                    'object' => $model->GetActivity()
                    ];
                $user=$model->User();
                Queue::push(new DistribuirFedi(false,$user,$json));
            }
            if ($model->APtype=='Collection')
            {
                $idmodelo=Str::plural(class_basename($model));
                Log::info("modelo: $idmodelo");
                if ($idmodelo=='Members')
                {
                    $old=$model->getOriginal('actividad');
                    if ($old!=$model->status)
                    {
                        if (($model->status=='admin') || ($model->status=='editor'))
                        {
                            if ($old=='Invite')
                            {
                                if ( parse_url(config('app.url'), PHP_URL_HOST) != parse_url($model->actor, PHP_URL_HOST))
                                {
                                    $activity=[
                                        '@context' => 'https://www.w3.org/ns/activitystreams',
                                        'type' => 'Accept',
                                        'actor' => $model->object, // equipo
                                        'object' => [
                                            'type' => 'Invite',
                                            'actor' => $model->actor, // campaña
                                            'object' => $model->object, // equipo
                                        ],
                                        'to' => [$model->actor]
                                    ];
                                    $user=explode("/", $model->object);
                                    $user=ActivityPub::GetIdentidadBySlug(array_pop($user));
                                    Queue::push(new EnviarActividadToActor($user,$this->actor,$activity));
                                }
                            }
                            if ($old=='Join')
                            {
                                if ( parse_url(config('app.url'), PHP_URL_HOST) != parse_url($model->object, PHP_URL_HOST))
                                {
                                    $activity=[
                                        '@context' => 'https://www.w3.org/ns/activitystreams',
                                        'type' => 'Accept',
                                        'actor' => $model->actor, // campaña
                                        'object' => [
                                            'type' => 'Join',
                                            'actor' => $model->object, // equipo
                                            'object' => $model->actor, // campaña
                                        ],
                                        'to' => [$model->object]
                                    ];
                                    $user=explode("/", $model->actor);
                                    $user=ActivityPub::GetIdentidadBySlug(array_pop($user));
                                    Queue::push(new EnviarActividadToActor($user,$this->object,$activity));
                                }
                            }
                        }
                    }
                }
            }
        });
        

    }

    public function GetActor($full=false)
    {
        if (isset($this->actor))
            $a=$this->actor;
        else
        {
            $idmodelo=Str::plural(class_basename($this));
            $team=Team::find($this->team_id);
            if ($team)
                $a=$team->GetActivity()['id'];
            else
            {
                $user=User::find($this->user_id);
                $a=$user->GetActivity()['id'];
            }
        }
        $ap=new ActivityPub();
        if ($full)
          return $ap->GetActorByUrl($a);
        else
          return $a;
    }

    public function User()
    {
            $idmodelo=Str::plural(class_basename($this));
            $team=Team::find($this->team_id);
            if ($team)
                return $team;
            $user=User::find($this->user_id);
            return $user;
        
    }

    public function GetActivity()
    {
        if (isset($this->APtranslate))
            foreach ($this->APtranslate as $key => $value)
                $this->$key = $this->$value;
        $conuser=['Note','Article','Announce','Question','Page'];
        if (in_array($this->APtype,$conuser))
        {
            if ($this->team_id)
                $user=Team::find($this->team_id);
            else
                $user=User::find($this->user_id);
        }
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
        if ($this->APtype=='Group')
        {
            $campaign=($this->type=='Campaign')?true:false;
            $activity = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'id' => route('activitypub.actor', ['slug' => $this->slug]),
                'url' => route('fediverso.profile', ['slug' => $this->slug]),// aqui podríamos tener la url de la campaña
                'type' => 'Group',
                'preferredUsername' => $this->slug,
                'published' => $this->created_at->toIso8601String(),
                'name' => $this->name,
                'campaign' => $campaign,
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

            if ($this->type=="Campaign")
            {
                $activity['url'] = route('campaigns.show', ['slug' => $this->slug]);
                $activity['members'] = route('activitypub.members', ['slug' => $this->slug]);
            }

            $converter = new CommonMarkConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
            if ($this->summary) $activity['summary'] = $converter->convert($this->summary)->getContent();
            if ($this->content) $activity['content'] = $converter->convert($this->content)->getContent();
            if ($this->profile_photo_url)
            {
                $activity['icon'] = [
                    'type' => 'Image',
                    'mediaType' => 'image/png',
                    'url' => $this->profile_photo_url,
                ];
            }
            if ($this->image_url)
            {
                $activity['image'] = [
                    'type' => 'Image',
                    'mediaType' => 'image/png',
                    'url' => $this->image_url,
                ];
            }
        }
    
        $contexto=[
            'https://www.w3.org/ns/activitystreams',
            [
                    'ostatus' => 'http://ostatus.org#',
                    #'atomUri' => 'ostatus:atomUri',
                    #'inReplyToAtomUri' => 'ostatus:inReplyToAtomUri',
                    #'conversation' => 'ostatus:conversation',
                    'sensitive' => 'as:sensitive',
                    'toot' => 'http://joinmastodon.org/ns#',
                    #'votersCount' => 'toot:votersCount',
                    #'blurhash' => 'toot:blurhash',
                    #'focalPoint' => 'Array',
                    #    (
                    #        [@container] => @list
                    #        [@id] => toot:focalPoint
                    #    )

                    # [Hashtag] => as:Hashtag

            ]
        ];


        if ($this->APtype=='Article')
        {
            $idmodelo=Str::plural(Str::lower(class_basename($this)));
            $activity = [
                '@context' => $contexto,
                'type' => 'Article',
                'id' => Route($idmodelo.'.show', ['slug' => $this->slug]),
                'url' => Route($idmodelo.'.show', ['slug' => $this->slug]),
                'attributedTo' => Route('activitypub.actor', ['slug' => $user->slug]),
                'to' => 'https://www.w3.org/ns/activitystreams#Public',
                // 'cc' => Aquí necesitamos un endpoint para los seguidores de este usuario
                'published' => $this->created_at->toIso8601String(),
                'updated' => $this->updated_at->toIso8601String(),
                'mediaType' => 'text/html',
                'attachment' => $this->getActivityPubAttachments()
            ];
            $converter = new CommonMarkConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
            if ($this->summary) $activity['summary'] = $converter->convert($this->summary)->getContent();
            if ($this->content) $activity['content'] = $converter->convert($this->content)->getContent();
        }





        if ($this->APtype=='Event')
        {
            $idmodelo=Str::plural(Str::lower(class_basename($this)));
            $activity = [
                '@context' => $contexto,
                'type' => 'Event',
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
                'attachment' => $this->getActivityPubAttachments()
            ];

            $converter = new CommonMarkConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
            if ($this->name) $activity['name'] = $this->name;
            if ($this->summary) $activity['summary'] = $converter->convert($this->summary)->getContent();
            if ($this->content) $activity['content'] = $converter->convert($this->content)->getContent();
            $activity['startTime'] = $this->startTime->toIso8601String();
            if ($this->endTime) $activity['endTime'] = $this->endTime->toIso8601String();

            $activity['location'] = [
                'address'=>[
                    'addressCountry' => $this->location_addressCountry,
                    'addressLocality' => $this->location_addressLocality,
                    'addressRegion' =>$this->location_addressRegion,
                    'postalCode' => $this->location_postalCode,
                    'streetAddress' => $this->location_streetAddress,
                    'type' => 'PostalAddress'
                ],
                'latitude' => $this->coordinates->latitude,
                'longitude' => $this->coordinates->longitude,
                'name' => $this->location_name,
                'type' => 'Place'
            ];
            if ($this->content) $activity['content'] = $converter->convert($this->content)->getContent();
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
            $activity = [
                '@context' => $contexto,
                'type' => 'Note',
                'id' => Route($idmodelo.'.show', ['slug' => $this->slug]),
                'url' => Route($idmodelo.'.show', ['slug' => $this->slug]),
                'attributedTo' => Route('activitypub.actor', ['slug' => $user->slug]),
                'to' => 'https://www.w3.org/ns/activitystreams#Public',
                // 'cc' => Aquí necesitamos un endpoint para los seguidores de este usuario
                'published' => $this->created_at->toIso8601String(),
                'updated' => $this->updated_at->toIso8601String(),
                'sensitive' => $this->sensitive,
                'summary' => $this->summary,
                'content' => $this->content,
                'mediaType' => 'text/html',
                'attachment' => $this->getActivityPubAttachments()
            ];
        }
        $validos=['Note','Article','Announce','Question','Page'];
        if (in_array($this->APtype,$validos))
        {
            $numlikes=Like::where('object',Route($idmodelo.'.show', ['slug' => $this->slug]))->count();
            $numannounces=Announce::where('object',Route($idmodelo.'.show', ['slug' => $this->slug]))->count();
            $activity['likes'] = [
                'id' => Route($idmodelo.'.show', ['slug' => $this->slug]).'/likes',
                'type' => 'Collection',
                'totalItems' => $numlikes
            ];
            $activity['shares'] = [
                'id' => Route($idmodelo.'.show', ['slug' => $this->slug]).'/shares',
                'type' => 'Collection', 
                'totalItems' => $numannounces
            ];
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
        // esto vamos a dejar de usarlo y vamos a hacerlo directamente desde el boot de create
        if ($this->team_id)
            $user=Team::find($this->team_id);
        else
            $user=User::find($this->user_id);
        if (($this->APtype=='Announce') || ($this->APtype=='Block') )
        {
            // Esto debe ir en el boot
            Log::info('comprobamos si la actividad está creada por nuestra instancia');
            // Solo distribuimos los impulsos que sean creados en local
            if  ( parse_url(config('app.url'), PHP_URL_HOST) != parse_url($this->actor, PHP_URL_HOST))
                return false;
            Log::info('distribuye '.$this->APtype.' porque no es recibido, lo crea un usuario local');
            $slug=explode('/',$this->actor);
            $slug=$slug[count($slug)-1];
            $user=$this->SlugToUser($slug);
            Log::info("El usuario tiene id ".$user->id);
            if ($this->APtype=='Block')
            {
                // los bloqueos no se distribuyen a los seguidores
                Queue::push(new EnviarActividadToActor($user,$this->object,$this->GetActivity()));
                return true;
            }
        }
        if ($activity)
            $mod=false;
        else
            $mod=$this;
        if ($mod===false)
            Log::info("se envia por activity");
        else
            Log::info("se envia por model");
        Log::info("el user es ".$user->slug);
        Queue::push(new DistribuirFedi($mod,$user,$activity));
    }

    public function SlugToUser($slug)
    {
        $user=User::where('slug', $slug)->first();
        if (!($user)) $user=Team::where('slug', $slug)->first();
        if (!($user)) return false;
        return $user;
    }

    public function Collection($listado,$url,$desc=true): JsonResponse
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
        if ($desc)
            $res = $listado->orderBy('id','desc')->paginate(20);
        else
            $res = $listado->orderBy('id')->paginate(20);
        $listado->get();
        $list = [];
        foreach ($res as $item) $list[] = $item->object;
        Log::info(print_r($list,1));
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

    
    
    public function editable()
    {
        // devuelve true si el usuario logueado es creador o parte del equipo del modelo
        $user=Auth::user();
        if ($user)
        {
            if ($user->id===$this->user_id) return true;
            foreach ($user->teams as $team)
                if ($team->id===$this->team_id) return true;
        }
        return false;
    }



    public function apfiles()
    {
        return $this->morphMany(Apfile::class, 'apfileable');
    }

    public function getActivityPubAttachments(): array
    {
        return $this->apfiles->map(function ($file) {
            return [
                #'type' => $this->getApfileType($file->file_type),
                'type' => 'Document',
                'mediaType' => $file->file_type,
                'url' => asset("storage/{$file->file_path}"),
                'name'=> $file->alt_text,
                'summary'=> $file->alt_text,
                'blurhash'=>'UFO|U[~pM{t89F?bWBM|t7WBRjt7xuWCofRj'
                    
            ];
        })->toArray();
    }

    private function getApfileType(string $mimeType): string
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => 'Image',
            str_starts_with($mimeType, 'video/') => 'Video',
            str_starts_with($mimeType, 'audio/') => 'Audio',
            default => 'Document',
        };
    }


}
