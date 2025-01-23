<?php

namespace App\ActivityPub;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Apfollower;
use App\Models\Apfollowing;
use App\Models\Timeline;
use App\Models\Like;
use App\Models\Announce;



use Illuminate\Support\Facades\Cache;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

use App\ActivityPub\HTTPSignature;
use App\ActivityPub\ActivityPub;
use App\Jobs\SendActivity;

use HTMLPurifier;
use HTMLPurifier_Config;
use DateTime;


class ActivityPub 
{
    static function GetActorByUrl($user,$url)
    {
        if(!\p3k\url\is_url($url)) return false;
        
        $key="-actor-".$url;
        if ($out=Cache::get($key)) 
        {
          if (isset($out['userfediverso']))
            return $out;
        }
        $out=self::GetObjectByUrl($user,$url);
        if (is_null($out)) return ['error'=>'response null'];
        if (isset($out['error'])) return $out;
        if (isset($out['following'])) $out['countfollowing']=self::GetColeccion($user,$out['following'],true);
        if (isset($out['followers'])) $out['countfollowers']=self::GetColeccion($user,$out['followers'],true);
        $d=explode("/",$url)[2];
        if (isset($out['preferredUsername']))
            $out['userfediverso']=$out['preferredUsername']."@$d";
        else
            return false;
        Cache::put($key,$out,3600*24*5);
        return $out;
    }

    static function GetObjectByUrl($user,$url,$cache=300)
    {
        // hay que revisar esta política de cache, guardar en caché pública solo objetos públicos, distinto ttl según type del objeto
        if (is_array($url)) return $url;
        if ($user)
            $key=$user->id."-o-".$url;
        else
            $key=$url;
        if ($out=Cache::get($key))
            return $out;
        if ($out=Cache::get($url))
            return $out;
        $d=explode("/",$url)[2];
        $idca="$d ".date("Y-m-d H").( (int)(date('i')/5)); // 5 minutos
        $num=(int)Cache::get($idca);
        if ($num++>100) return ['error'=>"muchas peticiones a $d"]; //    150 parecen muchas, con 100 va piano
        Cache::put($idca,$num,3600);
        $out=self::GetUrlFirmado($user,$url);
        if (!(is_array($out)))
        {
            $out=['error'=>$out];
            Cache::put($key,$out,120);
            return $out;
        }
        if (isset($out['error']))
        {
            Cache::put($key,$out,120);
        }
        else
        {
            Cache::put($key,$out,$cache*60);
            if ($key!=$url) Cache::put($key,$out,$cache*60);
        }
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
                    Cache::put($idcache,$actor,3600*24);
                    $url=false;
                    if (isset($actor['links']))
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

        if ($actor['id']==$user->GetActivity()['id'])
            return false;
        $Follow = new Apfollowing();
        $Follow->object = $actor['id'];
        $Follow->actor = $user->GetActivity()['id'];
        $Follow->save();
        $id=$Follow->id;
        $activity=[
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => route('activitypub.actor', ['slug' => $user->slug]).'/'.$id,
            'type' => 'Follow',
            'actor' => route('activitypub.actor', ['slug' => $user->slug]),
            'object' => $actor['id']
        ];
        $activity=json_encode($activity);
        Log::info('inbox: '.$actor['inbox']);
        $response=self::EnviarActividadPOST($user,$activity,$actor['inbox']);
        if (((string)$response)[0]!='2')
        {
            $Follow->delete();
            return false;
        }
        return true;
    }
    
    static function dejarDeSeguir($user,$actor)
    {
        $Follow = Apfollowing::where('object', $actor['id'])->where('actor', $user->GetActivity()['id'])->first();
        if ($Follow)
        {
            $id=$Follow->id;
            Log::info('Dejar de seguir a '.$actor['id'].' nº registro '.$id);
            Apfollowing::where('object', $actor['id'])->where('actor', $user->GetActivity()['id'])->delete();
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
            if (((string)$response)[0]!='2')
            {
                Log::info("ERROR hay que controlar aqui codigo respuesta $response y mandarlo a un job");
                return false;
            }
            return true;
        }
        return false;
    }

    static function siguiendo($user,$actor)
    {
        $id=$actor['id'];
        $Follow = Apfollowing::where('object', $id)->where('actor', $user->GetActivity()['id'])->first();
        if ($Follow)
            return true;
        return false;
    }

    static function tesigue($user,$actor)
    {
        $id=$actor['id'];
        $Follow = Apfollower::where('object', $id)->where('actor', $user->GetActivity()['id'])->first();
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
            if (!(isset($outbox['orderedItems']))) Log::info('489974857349'.print_r($outbox,1));
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
        return $list;
    }

    static function GetColeccion($user,$idlist,$solocount=false)
    {
        if (is_string($idlist))
            $col=self::GetObjectByUrl($user,$idlist,3);
        else
            $col=$idlist;
        if (array_is_list($col)) 
        {
            if ($solocount) return count($col);
            return $col;
        }
        if ($solocount) if (isset($col['totalItems'])) return $col['totalItems'];
        if ((isset($col['error'])) && ($solocount)) return "?";
        if (isset($col['error'])) return $col;
        if  ((isset($col['type'])) &&  ( ($col['type']=='Collection') ||   ($col['type']=='OrderedCollection') )  ) 
        {
            if (isset($col['first'])) // puede ser que nos de el nº pero no estén visibles los elementos
            {
                $col=self::GetObjectByUrl($user,$col['first']);
                if (isset($col['error']))
                {
                    if ($solocount) return "?";
                    return $col;
                }
                if (isset($col['items']))
                    $items=$col['items'];
                else
                    $items=[];
                while (isset($col['next']))
                {
                    $col=self::GetObjectByUrl($user,$col['next'],10);
                    if (isset($col['error']))
                    {
                        if ($solocount) return "?";
                        return $col;
                    }
                    if (isset($col['items']))
                        foreach ($col['items'] as $i)
                        {
                            if (is_string($i))
                                $items[]=$i;
                            else
                                $items[]=$i['id'];
                        }
                    if ($solocount)
                    if (count($items)>10000) 
                      return " > 10k ";
                    
                }
                if ($solocount) return count($items);
                return $items;
            }
/*            

hay colecciones que no tienen ni items ni número de items

Este es un ejemplo de lo que nos hemos encontrado

[2025-01-21 14:59:16] production.INFO: Array
(
    [@context] => https://www.w3.org/ns/activitystreams
    [id] => https://infosec.exchange/users/xxxxxxxx/followers
    [type] => OrderedCollection
)
*/

            return false;
        }
        else
        {
            Log::info($idlist);
            Log::info(print_R($col,1));
            Log::error('3459749543');
            return false;
        }
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
                Apfollower::where('object', $activity['actor'])->where('actor', $user->GetActivity()['id'])->delete();
                $apFollow = new Apfollower();
                $apFollow->object = $activity['actor'];
                $apFollow->actor = $user->GetActivity()['id'];
                $apFollow->save();
                $activity=[
                    '@context' => 'https://www.w3.org/ns/activitystreams',
                    // esta ruta no existe pero es única
                    'id' => route('activitypub.actor', ['slug' => $user->slug]).'/'.$apFollow->id,
                    'type' => 'Accept',
                    'actor' => route('activitypub.actor', ['slug' => $user->slug]),
                    'object' => $activity['id']
                ];
                /*
                $activity=json_encode($activity);
                $response=self::EnviarActividadPOST($user,$activity,$actor['inbox']);
                if (strlen($response)!=3) 
                    Log::error('Respuesta a accept follow: '.print_r($response,1));
                $response="$response";
                if ($response[0]!='2')
                {
                  Log::error('Respuesta _a_ follow erronea: '.print_r($response,1));
                  return response()->json(['error'=>$response[0]],400);
                }
                */
                /* 
                   Uso un Job para mandar la actividad, porque no puedo aceptar 
                   la solicitud de follow antes de responder a la petición 
                */
                SendActivity::dispatch(['activity'=>$activity,'user'=>$user,'to'=>$url]);
                return response()->json(['message' => 'Follow request received'],202);
            case 'Undo':
            {
                switch ($activity["object"]["type"]) {
                    case 'Follow':
                        Apfollower::where('object', $activity['actor'])->where('actor', $user->GetActivity()['id'])->delete();
                        return response()->json(['message' => 'Follow request received'],202);
                    case 'Announce':
                        Timeline::where('actor_id', $activity['actor'])->where('activity',$activity["object"]["id"])->where('user_id', $user->id)->delete();
                        Announce::where('actor',$activity['actor'])->where('object',$activity['object']['object'])->delete();
                        return response()->json(['message' => 'Undo request received'],202);
                    case 'Like':
                        Like::where('actor',$activity['actor'])->where('object',$activity["object"]["object"])->delete();
                        return response()->json(['message' => 'Undo request received'],202);
                    default:
                        Log::info(print_r($activity,1));
                        Log::info('Unknown activity type (undo): ' . $activity['type'] . '/' . $activity["object"]["type"]);
                        return response()->json(['message' => 'Unknow activity '.$activity['type']],202);
                }
            }
            case 'Accept':
            {
                switch ($activity["object"]["type"]) {
                    case 'Follow':
                        Apfollowing::where('object', $activity['actor'])->where('actor', $user->GetActivity()['id'])->update(['accept' => true]);
                        return response()->json(['message' => 'Accept'],202);
                    default:
                        Log::info(print_r($activity,1));
                        Log::info('Unknown activity type: (accept)' . $activity['type'] . '/' . $activity["object"]["type"]);
                        return response()->json(['message' => 'Unknow activity '.$activity['type'] . '/' . $activity["object"]["type"]],202);
                }
            }
            case 'Create':
            {
                // Compruebo si el actor está entre los seguidos del usuario
                $seguido=Apfollowing::where('object', $activity['actor'])->where('actor', $user->GetActivity()['id'])->first();
                if (is_null($seguido))
                {
                    if (isset($activity['object']['inReplyTo']))
                    {
                        // Como nos notifican de un nuevo comentario, borramos los replies o el propio objeto, a esto hay que darle una vuelta
                        $url=$activity['object']['inReplyTo'];
                        $publicacion=self::GetObjectByUrl($user,$url);
                        if (!is_null($publicacion))
                        {   
                            $replies=$publicacion['replies'];
                            Log::info('borro cache de replies');
                            if (is_array($replies))
                                Cache::forget($url);
                            else
                                Cache::forget($replies);
                        }

                    }
                    else
                    {
                        Log::info(print_r($activity,1));
                        Log::warning('El actor ' . $activity['actor'] . ' NO es seguido por el usuario ' . $user->id . 'y nos está mandando cosas');
                    }
                    return response()->json(['message' => 'Accept'],202);
                }
                else
                {
                    // un create de un actor a el que seguimos lo incluimos en el timeline siempre, si después la actividad es erronea o lo que sea lo vermos después
                    if ( $activity["object"]["attributedTo"] != $activity['actor'] )
                    {
                        Log::error(" distinto actor y attributedTo ".$activity["object"]["attributedTo"] . ' ' . $activity['actor'] );
                        return response()->json(['message' => 'Bad Request'],400);
                    }
                    $line= new Timeline();
                    if ($user instanceof User) $line->user_id=$user->id;
                    if ($user instanceof Team) $line->team_id=$user->id;
                    $line->actor_id=$activity['actor'];
                    $line->activity=$activity["object"]['id'];
                    // guardo en cache la actividad
                    Cache::put($activity["object"]['id'],$activity["object"],3600*8);
                    

                    // Aqui gestionamos la actividad, esto es, según el tipo si queremos añadirl a colecciones publicas, incluir un evento en la agenda, procesar HastTags, etc.
                    
                    
                    // Aqui gestionamos la actividad en función del tipo
                    switch ($activity["object"]["type"]) {
                        case 'Note':
                            break;
                    }
                    return response()->json(['message' => 'Accept'],202);
                }
            }
            case 'Announce':
            {
                $seguido=Apfollowing::where('object', $activity['actor'])->where('actor', $user->GetActivity()['id'])->first();
                if ($seguido)
                {
                    $line= new Timeline();
                    if ($user instanceof User) $line->user_id=$user->id;
                    if ($user instanceof Team) $line->team_id=$user->id;
                    $line->activity=$activity['id'];
                    $line->actor_id=$activity['actor'];
                    Cache::put($activity['id'],$activity,3600*8);
                    $line->save();
                }
                if (is_array($activity['object']))
                    $id=$activity['object']['id'];
                else
                    $id=$activity['object'];    
                if (self::IsLocal($id))
                {
                    Announce::firstOrCreate(['actor'=>$activity['actor'],'object'=>$activity['object']]);
                }
                return response()->json(['message' => 'Accept'],202);
            }
            case 'Like':
            {
                Log::info('Petición de Like '.print_r($activity,1));
                Like::firstOrCreate(['actor'=>$activity['actor'],'object'=>$activity['object']]);
                Log::info('Petición de Like ok');
                return response()->json(['message' => 'OK'],200);
            }
            case 'Delete':
            {
                if ($activity['actor']==$activity['object'])
                {
                    Cache::put($activity['object'],['error'=>'Deleted'],3600*24*30);
                    Cache::put($user->id.'-'.$activity['object'],['error'=>'Deleted'],3600*24*30);
                    TimeLine::where('activity', $activity['object'])->where('actor_id',$activity['actor'])->delete();
                    return response()->json(['message' => 'Accepted'],202);
                }
                if (isset($activity['object']['id']))
                {
                    Timeline::where('activity', $activity['object']['id'])->where('actor_id',$activity['actor'])->delete();
                    return response()->json(['message' => 'Accepted'],202);
                }
                Log::info('Petición de Delete '.print_r($activity,1));
                return response()->json(['message' => 'No implementado'],501);
            }
            case 'Update':
                $seguido=Apfollowing::where('object', $activity['actor'])->where('actor', $user->GetActivity()['id'])->first();
                if (is_null($seguido))
                    Log::warning('El actor ' . $activity['actor'] . ' NO es seguido por el usuario ' . $user->id);
                else
                {
                    if (isset($activity['object']['id']))
                    {
                        Cache::forget($activity['object']['id']);
                        Cache::forget($user->id.'-'.$activity['object']['id']);
                        Timeline::where('activity', $activity['object']['id'])->where('actor_id',$activity['actor'])->delete();
                        $line= new Timeline();
                        if ($user instanceof User) $line->user_id=$user->id;
                        if ($user instanceof Team) $line->team_id=$user->id;
                        $line->actor_id=$activity['actor'];
                        $line->activity=$activity["object"]['id'];
                        Cache::put($activity["object"]['id'],$activity["object"],3600*8);
                        $line->save();
                        return response()->json(['message' => 'Accept'],202);
                    }
                    Log::info('Update activity: '.print_r($activity,1));
                }
                return response()->json(['message' => 'No implementado'],501);
            default:
                Log::info('Unknown activity type root: ' . $activity['type']);
                Log::info(print_r($activity,1));
                return response()->json(['message' => 'Unknow activity '.$activity['type']],501);
        }
        Log::info(print_r($activity,1));
        Log::info('Aquí no deberíamos llegar nunca, debemos devolver siempre una respuesta http');
        return true;
    }



    static function EnviarActividadPOST($user,$json,$inbox)
    {
        if(!\p3k\url\is_url($inbox)) return false; 
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
    }

    static function GetUrlFirmado($user,$url)
    {
        if(!\p3k\url\is_url($url)) return false; 
        if (!($user))
        {
            Log::info($user);
            // La misma petición pero sin firmar
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'patalata.net'); // Agent
            $date = new DateTime('UTC');
            $headers = [
                #'(request-target)' => 'get '.parse_url($url, PHP_URL_PATH),
                #'Date' => $date->format('D, d M Y H:i:s \G\M\T'),
                #'Host' => parse_url($url, PHP_URL_HOST),
                'Accept' => 'application/jrd+json, application/ld+json, application/json' ,
                'Content-Type' => 'application/json',
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                Log::info('error curl',[curl_errno($ch),curl_error($ch)]);
                $codigo=curl_getinfo($ch, CURLINFO_HTTP_CODE);
                return ['error'=>curl_error($ch),'errorhttp'=>$codigo];
            }
            curl_close($ch);
            Log::info('resX '.$url."\n".print_r($response,1));
            list($responseHeaders, $responseBody) = explode("\r\n\r\n", $response, 2);
            Log::info("body $responseBody".print_r(json_decode($response,1),1));
            return json_decode($responseBody,1);
        }

        $headers = HTTPSignature::sign($user, false, $url); // Usamos una cadena vacía como cuerpo para GET
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Aplicar los encabezados firmados
        curl_setopt($ch, CURLOPT_USERAGENT, 'patalata.net'); // Agent
        curl_setopt($ch, CURLOPT_HEADER, true); // Incluir los encabezados en la respuesta
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $codigo=curl_getinfo($ch, CURLINFO_HTTP_CODE);
            Log::info('error curl',[curl_errno($ch),curl_error($ch),$codigo]);
            Log::info('dio error '.print_r($headers,1));
            return ['error'=>curl_error($ch),'errorhttp'=>$codigo];
        }
        curl_close($ch);
        list($responseHeaders, $responseBody) = explode("\r\n\r\n", $response, 2);
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

    static function IsLocal($url)
    {
        if (parse_url($url, PHP_URL_HOST) == parse_url(env('APP_URL'), PHP_URL_HOST))
            return true;
        else
            return false;
    }

    static function like($user,$id)
    {
        Log::info("like a $id");
        $actor=$user->GetActivity()['id'];
        $obj=self::GetObjectByUrl($user,$id);
        if (isset($obj['error']))
        {
            Log::info("error: ".print_r($obj,1));
            return false;
        }
        $activity=[
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => route('activitypub.actor', ['slug' => $user->slug]).'/'.$id,
            'type' => 'Like',
            'actor' => $actor,
            'object' => $id
        ];
        $activity=json_encode($activity);
        $response=self::EnviarActividadPOST($user,$activity,$obj['inbox']);
        if (((string)$response)[0]!='2')
        {
            return false;
        }
        $like=new Like();
        $like->actor=$actor;
        $like->object=$id;
        $like->save();
        return true;
    }


}

