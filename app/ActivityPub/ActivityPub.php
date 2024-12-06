<?php

namespace App\ActivityPub;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Apfollower;
use App\Models\Apfollowing;
use App\Models\Timeline;

use Illuminate\Support\Facades\Cache;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

use App\ActivityPub\HTTPSignature;
use App\ActivityPub\ActivityPub;

use HTMLPurifier;
use HTMLPurifier_Config;


class ActivityPub 
{
    static function getActor($user): JsonResponse
    {
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
            Log::error('Error al obtener el actor de '.$username);
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
        Log::info("devolver true o false segun el codigo de response $response");
        return true;
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
            Log::info("hay que controlar aqui codigo respuesta $response");
            Apfollowing::where('actor_id', $id)->where('user_id', $user->id)->delete();
            return true;
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

    static function tesigue($user,$actor)
    {
        $id=$actor['id'];
        $Follow = Apfollower::where('actor_id', $id)->where('user_id', $user->id)->first();
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
                #$outbox=self::GetUrlFirmado($user,$outbox['next']);
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
        if (isset($activity["object"]["attributedTo"]))
                        if ( $activity["object"]["attributedTo"] != $activity['actor'] )
                        {
                            Log::error(" distinto actor y attributedTo ".$activity["object"]["attributedTo"] . ' ' . $activity['actor'].print_r($activity,1) );
                            return response()->json(['error'=>'actor not equal attributedTo'],400);
                        }



        switch($activity['type']) {
            case 'Follow':
                $url=$activity['actor'];
                $actor = self::GetActorByUrl($user,$url);
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
                $activity=json_encode($activity);
                $response=self::EnviarActividadPOST($user,$activity,$actor['inbox']);
                Log::warning('hay que gestionar la respuesta si falla mandar aceptar follow: '.print_r($response,1));
                return response()->json(['message' => 'Follow request received'],202);
            case 'Undo':
            {
                switch ($activity["object"]["type"]) {
                    case 'Follow':
                        Apfollower::where('actor_id', $activity['actor'])->where('user_id', $user->id)->delete();
                        return response()->json(['message' => 'Follow request received'],202);
                    default:
                        Log::info('Unknown activity type: ' . $activity['type'] . '/' . $activity["object"]["type"]);
                        return response()->json(['message' => 'Unknow activity '.$activity['type']],202);
                }
            }
            case 'Accept':
            {
                switch ($activity["object"]["type"]) {
                    case 'Follow':
                        Log::info('Petición de Accept de Follow de '.$activity['actor']);
                        // pongo Apfollowing - accept a true
                        Apfollowing::where('actor_id', $activity['actor'])->where('user_id', $user->id)->update(['accept' => true]);
                        return response()->json(['message' => 'Accept'],202);
                    default:
                        Log::info('Unknown activity type: ' . $activity['type'] . '/' . $activity["object"]["type"]);
                        return response()->json(['message' => 'Unknow activity '.$activity['type'] . '/' . $activity["object"]["type"]],202);
                }
            }
            case 'Create':
            {
                switch ($activity["object"]["type"]) {
                    case 'Note':
                        if ( $activity["object"]["attributedTo"] != $activity['actor'] )
                        {
                            Log::error(" distinto actor y attributedTo ".$activity["object"]["attributedTo"] . ' ' . $activity['actor'] );
                            return false;
                        }
                        $line= new Timeline();
                        if ($user instanceof User) $line->user_id=$user->id;
                        if ($user instanceof Team) $line->team_id=$user->id;
                        $line->activity=$activity["object"]['id'];
                        Cache::put($activity["object"]['id'],$activity["object"],60*8);
                        $line->save();
                        Log::info("Actividad guardada en el timeline");
                        return response()->json(['message' => 'Accept'],202);
                    default:
                        Log::info(print_r($activity,1));
                        Log::info('Unknown activity type: ' . $activity['type'] . '/' . $activity["object"]["type"]);
                        return response()->json(['message' => 'Unknow activity '.$activity['type'] . '/' . $activity["object"]["type"]],202);
                }
            }
            case 'Announce':
            {
                $line= new Timeline();
                if ($user instanceof User) $line->user_id=$user->id;
                if ($user instanceof Team) $line->team_id=$user->id;
                $line->activity=$activity['id'];
                Cache::put($activity['id'],$activity,60*8);
                $line->save();
                Log::info("Actividad RT guardada en el timeline");
                return response()->json(['message' => 'Accept'],202);
            }
            default:
                Log::info('Unknown activity type: ' . $activity['type']);
                return response()->json(['message' => 'Unknow activity '.$activity['type']],501);
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
        $codigo=curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $codigo;
        #return $response;
    }

    static function GetUrlFirmado($user,$url)
    {
        $response=Cache::get($url);
        if ($response)
            return $response;
        if (!($user))
        {
            // La misma petición pero sin firmar
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            $response = curl_exec($ch);
            list($responseHeaders, $responseBody) = explode("\r\n\r\n", $response, 2);
            Cache::put($url,json_decode($responseBody,1),60*8);
            return json_decode($response,1);
        }
        $idcache=$user->id."-".$url;
        if ($out=Cache::get($idcache))
            return $out;
        // Generar encabezados firmados
        $headers = HTTPSignature::sign($user, false, $url); // Usamos una cadena vacía como cuerpo para GET
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
        Cache::put($idcache,json_decode($responseBody,1),60*8);
        Cache::put($url,json_decode($responseBody,1),60*8);
        return json_decode($responseBody,1); // Devolver el cuerpo de la respuesta
    }


    static function limpiarHtml($html)
    {
        // Configurar HTMLPurifier
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.SafeIframe', true);
        $config->set('HTML.SafeEmbed', true);
        $config->set('HTML.SafeObject', true);
        #$config->set('HTML.Allowed', 'p,b,strong,i,em,a[href|title|target],img[src|alt|title|width|height],ul,ol,li,br,span[style],h1,h2,h3,h4,h5,h6,blockquote,pre,code,table,thead,tbody,tr,td,th,video[src|type|width|height|controls|autoplay],audio[src|type|controls],iframe[src|width|height|frameborder|allowfullscreen]');
        $config->set('HTML.Allowed', 'p,b,strong,i,em,a[href|title|target],img[src|alt|title|width|height],ul,ol,li,br,span[style],h1,h2,h3,h4,h5,h6,blockquote,pre,code,table,thead,tbody,tr,td,th');
        $config->set('Attr.AllowedFrameTargets', ['_blank']); // Permitir abrir enlaces en otra ventana

        // Instanciar HTMLPurifier
        $purifier = new HTMLPurifier($config);
        $purifiedHtml = $purifier->purify($html);
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $purifiedHtml);
        $links = $dom->getElementsByTagName('a');
        foreach ($links as $link) 
        {
            $link->setAttribute('target', '_blank');
        }
        return $dom->saveHTML($dom->documentElement);    
    }


}

