<?php

namespace App\ActivityPub;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Apfollower;
use App\Models\Apfollowing;
use Illuminate\Support\Facades\Cache;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

use App\ActivityPub\HTTPSignature;
use App\ActivityPub\ActivityPub;


class ActivityPub 
{
    public function getActor($user): JsonResponse
    {
        Log::info('key public: ' . $user->public_key);

        // Construye el objeto Actor en formato JSON-LD
        $actor = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => route('activitypub.actor', ['slug' => $user->slug]),
            'type' => 'Person',
            'preferredUsername' => $user->slug,
            'name' => $user->name,
            'following' => route('activitypub.following', ['slug' => $user->slug]),
            'inbox' => route('activitypub.inbox', ['slug' => $user->slug]),
            'outbox' => route('activitypub.outbox', ['slug' => $user->slug]),            
            'publicKey' => [
                'id' => route('activitypub.actor', ['slug' => $user->slug]) . '#main-key',
                'owner' => route('activitypub.actor', ['slug' => $user->slug]),
                'publicKeyPem' => $user->public_key,
            ],
            'icon' => [
                'type' => 'Image',
                'mediaType' => 'image/png',
                'url' => $user->profile_photo_url,
            ],

        ];

        // Devuelve la respuesta en JSON
        return response()->json($actor, 200, ['Content-Type' => 'application/activity+json']);
    }

    static function GetActorByUrl($user,$url)
    {
        $key=$user->id."-".$url;
        if ($out=Cache::get($key))
            return $out;
        $out=self::GetUrlFirmado($user,$url);
        Cache::put($key,$out,60*24);
        return $out;
    }
    static function GetObjectByUrl($user,$url)
    {
        $key=$user->id."-o-".$url;
        if ($out=Cache::get($key))
            return $out;
        $out=self::GetUrlFirmado($user,$url);
        Cache::put($key,$out,60*24);
        return $out;
    }

    static function GetActorByUsername($user,$username)
    {
        $parts=explode("@",$username);
        if (count($parts)==2)
        {
            $name=$parts[0];
            $domain=strtolower($parts[1]);
            // compobamos si $domain es un dominio válido con regexp (letras, numeros, guiones y punto)
            if (preg_match('/^[a-z0-9.-]+$/',$domain))
            {
                $url='https://'.$domain.'/.well-known/webfinger?resource=acct:'.$name.'@'.$domain;
                if ($user)
                    $idcache=$user->id."-".$url;
                else
                    $idcache=$url;
                $actor=Cache::get($idcache);
                if (!$actor)
                    $actor=self::GetUrlFirmado($user,$url);
                if ($actor)
                {
                    Cache::put($idcache,$actor,60*24);
                    $url=false;
                    foreach ($actor['links'] as $link)
                    {
                        if ($url===false) // Si no hemos encontrado el self
                            if (isset($link['rel']) && $link['rel']=='self')
                                $url=$link['href'];
                        if ($link['rel']=='self')
                            if (isset($link['rel']) && $link['rel']=='self')
                                $url=$link['href'];
                    }
                    if ($url)
                    {
                        $actor=self::GetActorByUrl($user,$url);
                        return $actor;
                    }
                }
            }
            Log::info('Error al obtener el actor de '.$username);
            return false;
        }

    }

    static function seguir($user,$actor)
    {
        if ($actor['id']==route('activitypub.actor', ['slug' => $user->slug]))
            return false;
        // creo Apfollowing
        $Follow = new Apfollowing();
        $Follow->actor_id = $actor['id'];
        $Follow->user_id = $user->id;
        $Follow->save();
        $id=$Follow->id;
        Log::info('Seguir a '.$actor['id'].' nº registro '.$id);
        $activity=[
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => route('activitypub.actor', ['slug' => $user->slug]).'/'.$id,
            'type' => 'Follow',
            'actor' => route('activitypub.actor', ['slug' => $user->slug]),
            'object' => $actor['id']
        ];
        $activity=json_encode($activity);
        $response=self::EnviarActividadPOST($user,$activity,$actor['inbox']);
        return $response;
    }
    
    static function dejarDeSeguir($user,$actor)
    {
        $id=$actor['id'];
        $Follow = Apfollowing::where('actor_id', $id)->where('user_id', $user->id)->first();
        if ($Follow)
        {
            $id=$Follow->id;
            Log::info('Dejar de seguir a '.$actor['id'].' nº registro '.$id);
            $activity=[
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'id' => route('activitypub.actor', ['slug' => $user->slug]).'/'.$id,
                'type' => 'Undo',
                'actor' => route('activitypub.actor', ['slug' => $user->slug]),
                'object' => [
                    'type' => 'Follow',
                    'actor' => route('activitypub.actor', ['slug' => $user->slug]),
                    'object' => $actor['id']
                ]
            ];
            $activity=json_encode($activity);
            $response=self::EnviarActividadPOST($user,$activity,$actor['inbox']);
            Apfollowing::where('actor_id', $id)->where('user_id', $user->id)->delete();
            return $response;
        }
        return false;
    }

    static function siguiendo($user,$actor)
    {
        $id=$actor['id'];
        $Follow = Apfollowing::where('actor_id', $id)->where('user_id', $user->id)->first();
        if ($Follow)
            return true;
        return false;
    }

    static function GetOutbox($user,$actor,$limite=50)
    {
        if (!(isset($actor['outbox'])))
            return false;
        $outbox=$actor['outbox'];
        $outbox=self::GetUrlFirmado($user,$outbox);
        if (isset($outbox['orderedItems']))
            return $outbox['orderedItems'];
        $list=[];
        if (isset($outbox['first']))
        {
            $outbox=self::GetUrlFirmado($user,$outbox['first']);
            $list=$outbox['orderedItems'];
            while (isset($outbox['next']))
            {
                $outbox=self::GetUrlFirmado($user,$outbox['next']);
                $list=array_merge($list,$outbox['orderedItems']);
                if (count($list)>$limite)
                    return array_slice($list,0,$limite);
            }
            return $list;
        }
        return $out;
    }


    static function InBox($user,$activity)
    {
        // Aquí llega la petición con la firma verificada
        Log::info('ActivityPub InBox '.print_r($activity['type'],1));
        switch($activity['type']) {
            case 'Follow':
                $url=$activity['actor'];
                $actor = self::GetActorByUrl($user,$url);
                Log::debug('Petición de Follow de '.$url);
                Apfollower::where('actor_id', $activity['actor'])->where('user_id', $user->id)->delete();
                $apFollow = new Apfollower();
                $apFollow->actor_id = $activity['actor'];
                $apFollow->user_id = $user->id;
                $apFollow->save();
                // Guardo el follow, pero tengo que aceptarlo
                $activity=[
                    '@context' => 'https://www.w3.org/ns/activitystreams',
                    // esta ruta no existe
                    'id' => route('activitypub.actor', ['slug' => $user->slug]).'/'.$apFollow->id,
                    'type' => 'Accept',
                    'actor' => route('activitypub.actor', ['slug' => $user->slug]),
                    'object' => $activity['id']
                ];
                // enviar la actividad
                Log::info('Enviar aceptación de follow: '.print_r($activity,1));
                $activity=json_encode($activity);
                $response=self::EnviarActividadPOST($user,$activity,$actor['inbox']);
                Log::info('Respuesta: '.print_r($response,1));
                return true;
            case 'Undo':
            {
                switch ($activity["object"]["type"]) {
                    case 'Follow':
                        Log::info('Petición de Undo de Follow de '.$activity['actor']);
                        Log::info(print_r($activity,1));
                        Apfollower::where('actor_id', $activity['actor'])->where('user_id', $user->id)->delete();
                        return true;
                    default:
                        Log::info('Unknown activity type: ' . $activity['type'] . '/' . $activity["object"]["type"]);
                        return true;
                }
            }
            case 'Accept':
            {
                switch ($activity["object"]["type"]) {
                    case 'Follow':
                        Log::info('Petición de Accept de Follow de '.$activity['actor']);
                        // pongo Apfollowing - accept a true
                        Apfollowing::where('actor_id', $activity['actor'])->where('user_id', $user->id)->update(['accept' => true]);
                        return true;
                    default:
                        Log::info('Unknown activity type: ' . $activity['type'] . '/' . $activity["object"]["type"]);
                        return true;
                }
            }
            default:
                Log::info('Unknown activity type: ' . $activity['type']);
                return true;
        }
        return true;
    }



    static function EnviarActividadPOST($user,$json,$inbox)
    {
        $headers = HTTPSignature::sign($user, $json, $inbox);
        $ch = curl_init($inbox);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        $response=json_decode($response, true);
        return $response;
    }

    static function GetUrlFirmado($user,$url)
    {
        if (!($user))
        {
            // La misma petición pero sin firmar
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            $response = curl_exec($ch);
            return json_decode($response,1);

            
        }

        $id=$user->id."-".$url;
        if ($out=Cache::get($id))
            return $out;

        // Generar encabezados firmados
        $headers = HTTPSignature::sign($user, false, $url); // Usamos una cadena vacía como cuerpo para GET
        Log::info('URL (esto debería siempre cachearse): '.$url);
        $headers[]='Accept: application/json';
        // Inicializar cURL para la solicitud GET
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Aplicar los encabezados firmados
        curl_setopt($ch, CURLOPT_HEADER, true); // Incluir los encabezados en la respuesta
    
        // Ejecutar la solicitud
        $response = curl_exec($ch);
    
        // Manejo de errores
        if (curl_errno($ch)) {
            Log::error('Error en la solicitud firmada GET: '.curl_error($ch));
            return null;
        }
    
        curl_close($ch);
    
        // Dividir los encabezados del cuerpo de la respuesta
        list($responseHeaders, $responseBody) = explode("\r\n\r\n", $response, 2);
        Cache::put($id,json_decode($responseBody,1),60*24);
        

        return json_decode($responseBody,1); // Devolver el cuerpo de la respuesta
        
        

    }
}

