<?php

namespace App\ActivityPub;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Team;
use App\Models\Campaign;
use App\Models\Post;
use App\Models\Apfollower;
use App\Models\Apfollowing;
use App\Models\Timeline;
use App\Models\Like;
use App\Models\Announce;
use App\Models\Block;
use App\Models\Member;
use App\Models\Reply;


use Illuminate\Routing\Router;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Auth;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

use App\ActivityPub\HTTPSignature;
use App\ActivityPub\ActivityPub;
use App\Jobs\EnviarActividadToActor;

use HTMLPurifier;
use HTMLPurifier_Config;
use DateTime;

class ActivityPub 
{

    public $user=null;

    public function __construct($user=null)
    {
        $this->user = $user;
    }

    static function GetIdentidad($user=null)
    {
        if (is_null($user)) $user=Auth::user();
        if (!$user) return false;
        if ($user->current_team_id)
        {
            $team=Team::find($user->current_team_id);
            return $team;
        }
        return $user;
    }




    static function GetIdentidadBySlug($slug)
    {
        $user=User::where('slug',$slug)->first();
        if (!$user) $user=Team::where('slug',$slug)->first();
        if (!$user) $user=Campaign::where('slug',$slug)->first();
        return $user;
    }

    public function GetActorByUrl($url)
    {
        if(!\p3k\url\is_url($url)) return false;
        // Si el actor se borra 
        $key="-actor-".$url;
        if ($out=Cache::get($key)) 
        {
          if (isset($out['userfediverso'])) return $out;
        }
        $out=$this->GetObjectByUrl($url);
        if (is_null($out)) return ['error'=>'response null'];
        if (isset($out['error'])) return $out;
        if (isset($out['following'])) $out['countfollowing']=$this->GetColeccion($out['following'],true);
        if (isset($out['followers'])) $out['countfollowers']=$this->GetColeccion($out['followers'],true);
        $d=explode("/",$url)[2];
        if (isset($out['preferredUsername']))
            $out['userfediverso']=$out['preferredUsername']."@$d";
        else
        {   
            print_r($out);
            #$out['error']='Actor inválido';
            return $out;
        }
        Cache::put($key,$out,3600*24);
        return $out;
    }


    public function GetObjectByUrl($url,$cache=false)
    {
        if ($cache===false) $cache=60*24*15;
        // hay que revisar esta política de cache, guardar en caché pública solo objetos públicos, distinto ttl según type del objeto
        if (is_array($url)) return $url;
        if(!\p3k\url\is_url($url)) return false;
        if ($out=Cache::get($url)) return $out;
        $domain=parse_url($url, PHP_URL_HOST);
        if (false)
        if (parse_url($url, PHP_URL_HOST) == parse_url(env('APP_URL'), PHP_URL_HOST))
        {
            // esto provoca errroes 429
            $request = Request::get($url, 'GET');
            $response = app()->handle($request);
            $status = $response->getStatusCode();
            $body = $response->getContent();
            $out=json_decode($body,true);
            if ($out) return $out;
        }
        $idbantmp="idbantmp $domain";
        if ($out=Cache::get($idbantmp))
        {
          return $out;
        }
        $idca="$domain ".date("Y-m-d H").( (int)(date('i')/5)); // 5 minutos
        $num=(int)Cache::get($idca);
        #echo "    $num   ";
        if ($num++>100) Log::info("muchas peticiones a $domain ($num) ".date("YmdHis"));
        if ($num++>100) return ['error'=>"muchas peticiones a $domain ($num) ".date("YmdHis"),'codhttp'=>8080]; //    150 parecen muchas, con 100 va piano
        Cache::put($idca,$num,3600);
        Log::info("NoCache $url ($cache)");
        $out=$this->GetUrlFirmado($url);
        if (!(is_array($out)))
        {
            $out=['error'=>"No es array: $url - $out"];
            Log::info($out);
            Cache::put($url,$out,120);
            return $out;
        }
        if (isset($out['codhttp']))
            if ($out['codhttp']==429)
            {
                $out=['error'=>'temporal too may','codhttp'=>$out['codhttp']];
                Log::info($out);
                Cache::put($idbantmp,$out,60);
            }
        if (isset($out['errorcurl']))
        {
            if ($out['errorcurl']>0)
            {
                $out=['error'=>'curl','coderror'=>$out['errorcurl']];
                Log::info($out);
                Cache::put($idbantmp,$out,10);
            }
        }
        if (isset($out['error']))
        {
            Cache::put($url,$out,30);
        }
        else
        {
            Cache::put($url,$out,$cache*60);
        }
        return $out;
    }

    public function GetActorByUsername($username)
    {
        $username=trim($username);
        if (strlen($username<3)) return false;
        if ($username[0]=='@')
            $username=substr($username,1);
        $dica="actor by username $username";
        $actor=Cache::get($dica);
        if ($actor) return $actor;
        $parts=explode("@",$username);
        if (count($parts)==2)
        {
            $dica="actor 2 by username $username";
            $name=$parts[0];
            $domain=strtolower($parts[1]);
            // compobamos si $domain es un dominio válido con regexp (letras, numeros, guiones y punto)
            if (preg_match('/^[a-z0-9.-]+$/',$domain))
            {
                $url='https://'.$domain.'/.well-known/webfinger?resource=acct:'.$name.'@'.$domain;
                $idcache=$url;
                $actor=Cache::get($idcache);
                if (!$actor)
                    $actor=$this->GetUrlFirmado($url);
                if ($actor)
                {
                    Cache::put($idcache,$actor,3600*24*30);
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
                        $actor=$this->GetActorByUrl($url);
                        if (!(isset($actor['error'])))
                            Cache::put($dica,$actor,3600*24*15);
                        return $actor;
                    }
                }
            }
            return false;
        }
    }

    public function seguir($actor)
    {
        if ($actor['id']==$this->user->GetActivity()['id'])
            return false;
        $Follow = new Apfollowing();
        $Follow->object = $actor['id'];
        $Follow->actor = $this->user->GetActivity()['id'];
        $Follow->save();
        $id=$Follow->id;
        $activity=[
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => route('activitypub.actor', ['slug' => $this->user->slug]).'/'.$id,
            'type' => 'Follow',
            'actor' => route('activitypub.actor', ['slug' => $this->user->slug]),
            'object' => $actor['id']
        ];
        $activity=json_encode($activity);
        $response=$this->EnviarActividadPOST($activity,$actor['inbox']);
        if (((string)$response)[0]!='2')
        {            
            $Follow->delete();
            return false;
        }
        return true;
    }
    
    public function dejarDeSeguir($actor)
    {
        $Follow = Apfollowing::where('object', $actor['id'])->where('actor', $this->user->GetActivity()['id'])->first();
        if ($Follow)
        {
            $id=$Follow->id;
            Apfollowing::where('object', $actor['id'])->where('actor', $this->user->GetActivity()['id'])->delete();
            $activity=[
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'id' => route('activitypub.actor', ['slug' => $this->user->slug]).'/'.$id,
                'type' => 'Undo',
                'actor' => route('activitypub.actor', ['slug' => $this->user->slug]),
                'object' => [
                    'type' => 'Follow',
                    'actor' => route('activitypub.actor', ['slug' => $this->user->slug]),
                    'object' => $actor['id']
                ]
            ];
            $activity=json_encode($activity);
            $response=$this->EnviarActividadPOST($activity,$actor['inbox']);
            if (((string)$response)[0]!='2')
            {
                Log::info("ERROR hay que controlar aqui codigo respuesta $response y mandarlo a un job");
                return false;
            }
            return true;
        }
        return false;
    }

    public function siguiendo($actor)
    {
        $id=$actor['id'];
        $Follow = Apfollowing::where('object', $id)->where('actor', $this->user->GetActivity()['id'])->first();
        if ($Follow)
            return true;
        return false;
    }

    public function tesigue($actor)
    {
        $id=$actor['id'];
        $Follow = Apfollower::where('object', $id)->where('actor', $this->user->GetActivity()['id'])->first();
        if ($Follow)
            return true;
        return false;
    }

    public function GetOutbox($actor,$limite=50)
    {

        if (!(isset($actor['outbox'])))
            return false;
        $outbox=$actor['outbox'];
        $outbox=$this->GetUrlFirmado($this->user,$outbox);
        if (isset($outbox['orderedItems']))
            return $outbox['orderedItems'];
        $list=[];
        if (isset($outbox['first']))
        {
            $outbox=$this->GetUrlFirmado($this->user,$outbox['first']);
            if (!(isset($outbox['orderedItems']))) Log::info('489974857349'.print_r($outbox,1));
            $list=$outbox['orderedItems'];
            while (isset($outbox['next']))
            {
                #$outbox=$this->GetUrlFirmado($this->user,$outbox['next']);
                $list=array_merge($list,$outbox['orderedItems']);
                if (count($list)>$limite)
                    return array_slice($list,0,$limite);
            }
            return $list;
        }
        return $list;
    }

    public function GetCountCollection($idlist)
    {
        $idcache="gc  ".md5(json_encode($idlist));
        $num=Cache::get($idcache);
        if ($num) return $num;
        $obj=$this->GetObjectByUrl($idlist,5); // 5 minutos de ttl
        #Log::info(print_r($obj,1));
        if (array_is_list($obj))
        {
            Cache::put($idcache,count($obj),60*5);
            return count($obj);
        }
        if (isset($obj['totalItems']))
        {
            Cache::put($idcache,$obj['totalItems'],60*5);
            return $obj['totalItems'];
        }
        if (isset($obj['first']))
        {
            if (is_array($obj['first']))
                if (isset($obj['first']['items']))
                    return count($obj['first']['items']);
            
        } 
        return false;
    }

    public function GetListCollection($idlist,$limite=0)
    {
        // esta función estaría guay que guardara en cache persistente y comprobara solo si hay nuevos
        $idcache="list collection  ".$idlist;
        $out=Cache::get($idcache);
        if ($out) Log::info($idcache);
        if ($out) Log::info(print_r($out,1));
        if ($out) return $out;
        $cachetmp="persistente $idlist";
        $cachetmp=Cache::get($cachetmp);
        if (!(is_array($cachetmp))) 
            $list=[];
        else
            $list=$cachetmp;
        $col=$this->GetObjectByUrl($idlist,5);
        Log::info('first col '.print_r($col,1));
        // esto tiene tipo collection y un first
        if ($col['type']!='Collection')
        {
            Log::error(print_r($col,1));
            return [];
        }

        if (isset($col['first']))
        {
            $seguir=true;
            $col=$this->GetObjectByUrl($col['first'],5);
            while ( (count($col['items'])>0)  &&  ($seguir))
            {
                if ($col['type']!='CollectionPage')
                {
                    Log::info("Revisar cuando ocurra este error, debe ser limite de peticiones, errores de conexión...");
                    Log::info(print_r($col,1));
                    return $list;
                }
                foreach ($col['items'] as $item)
                {
                    if (is_array($item)) $item=$item['id'];
                    /*
                        Esto está mal, puede que ya tengamos un elemento pero haya falta seguir, porque ese elemento lo 
                        hemos recibido en algún inbox de nuestros usuarios.
                        Tal vez en el listado persistente habría que apuntar cuales vienen de esta función, o cuando
                        recibamos un reply a nuestro inbox apuntarlo como "flotante" o algo así, de manera que aquí 
                        lo ignoremos a la hora de comprobar si hemos llegado al final
                    */
                    if (in_array($item,$list))
                        $seguir=false; // en cuanto me encuentro un elemento que ya teníamos significa que estamos sincronizados
                    else
                        $list=array_merge([$item],$list);
                }
                if (!(isset($col['next']))) $seguir=false;
                if (!(isset($col['next']))) Log::info(" me imagino que es normal pq llegadomos al final ".print_r($col,1));
                if ($seguir)
                {
                    $col=$this->GetObjectByUrl($col['next'],5);
                }
            }
        }
        Cache::put($cachetmp,$list,60*60*24*30*3);
        Cache::put($idcache,$list,5);
        return $list;
    }

    public function GetColeccion($idlist,$solocount=false,$limite=false)
    {
        if (is_string($idlist))
            $col=$this->GetObjectByUrl($idlist,3);
        else
            $col=$idlist;
        if (!is_array($col))
        {
          Log::error("No entiendo porque entramos aquí, idlist es $idlist");
        }
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
                $x=$col['first'];
                $col=$this->GetObjectByUrl($col['first']);
                if (isset($col['error']))
                {
                    if ($solocount) return "?";
                    return $col;
                }
                if (isset($col['items']))
                    $items=$col['items'];
                else
                    $items=[];
                if (isset($col['orderedItems']))
                    $items=$col['orderedItems'];
                while (isset($col['next']))
                {
                    $col=$this->GetObjectByUrl($col['next'],10);
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
                    if (isset($col['orderedItems']))
                        foreach ($col['orderedItems'] as $i)
                        {
                            if (is_string($i))
                                $items[]=$i;
                            else
                            {
                                $items[]=$i['id'];
                                Cache::Put($i['id'],$i,60*60*24);
                            }
                        }
                    if ($limite)
                    {
                        $items=array_slice($items,0,$limite);
                        return $items;   
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




    public function InBox($activity)
    {
        // Aquí llega la petición con la firma verificada
        #Log::info(print_r($activity,1));
        if (isset($activity["object"]["attributedTo"]))
            if ( $activity["object"]["attributedTo"] != $activity['actor'] )
            {
                Log::error(" distinto actor y attributedTo ".$activity["object"]["attributedTo"] . ' ' . $activity['actor'].print_r($activity,1) );
                return response()->json(['error'=>'actor not equal attributedTo'],400);
            }

            $txt="InBox ".$this->user->slug." $activity[type]: $activity[actor]";
            #Log::info(print_r($activity,1));
            if (isset($activity['object']))
                if (is_string($activity['object']))
                    $txt.="\t$activity[object]";
                else
                    if (isset($activity['object']['id']))
                        $txt.="\t".$activity['object']['id'];
            switch($activity['type']) {
            case 'Follow':
                $url=$activity['actor'];
                $actor = $this->GetActorByUrl($url);
                Apfollower::where('object', $activity['actor'])->where('actor', $this->user->GetActivity()['id'])->delete();
                $apFollow = new Apfollower();
                $apFollow->object = $activity['actor'];
                $apFollow->actor = $this->user->GetActivity()['id'];
                $apFollow->save();
                $activity=[
                    '@context' => 'https://www.w3.org/ns/activitystreams',
                    // esta ruta no existe pero es única
                    'id' => route('activitypub.actor', ['slug' => $this->user->slug]).'/'.$apFollow->id,
                    'type' => 'Accept',
                    'actor' => route('activitypub.actor', ['slug' => $this->user->slug]),
                    'object' => $activity
                ];
                Queue::push(new EnviarActividadToActor($this->user,$actor['id'],$activity));
                return response()->json(['message' => 'Follow request received'],202);
            case 'Undo':
            {
                switch ($activity["object"]["type"]) {
                    case 'Follow':
                        Apfollower::where('object', $activity['actor'])->where('actor', $this->user->GetActivity()['id'])->delete();
                        return response()->json(['message' => 'Follow request received'],202);
                    case 'Announce':
                        Timeline::where('actor_id', $activity['actor'])->where('activity',$activity["object"]["id"])->delete();
                        Announce::where('actor',$activity['actor'])->where('object',$activity['object']['object'])->delete();
                        return response()->json(['message' => 'Undo request received'],202);
                    case 'Like':
                        Like::where('actor',$activity['actor'])->where('object',$activity["object"]["object"])->delete();
                        return response()->json(['message' => 'Undo request received'],202);
                    case 'Block':
                        Log::info('undo block '.$activity['actor'].' '.$activity["object"]["object"]);
                        Block::where('actor',$activity['actor'])->where('object',$activity["object"]["object"])->delete();
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
                        Apfollowing::where('object', $activity['actor'])->where('actor', $this->user->GetActivity()['id'])->update(['accept' => true]);
                        return response()->json(['message' => 'Accept'],202);
                    case 'Invite':
                        Members::where('object', $activity['actor'])->where('actor', $this->user->GetActivity()['id'])->update(['status' => 'editor']);
                        return response()->json(['message' => 'Accept'],202);
                    case 'Join':
                        Members::where('actor', $activity['actor'])->where('object', $this->user->GetActivity()['id'])->update(['status' => 'editor']);
                        return response()->json(['message' => 'Accept'],202);
                    default:
                        Log::info(print_r($activity,1));
                        Log::info('Unknown activity type: (accept)' . $activity['type'] . '/' . $activity["object"]["type"]);
                        return response()->json(['message' => 'Unknow activity '.$activity['type'] . '/' . $activity["object"]["type"]],202);
                }
            }
            case 'Reject':
            {
                switch ($activity["object"]["type"]) {
                    case 'Follow':
                        Apfollowing::where('actor', $activity['actor'])->where('object', $this->user->GetActivity()['id'])->delete();
                        return response()->json(['message' => 'Accept'],202);
                    case 'Invite':
                        Members::where('object', $activity['actor'])->where('actor', $this->user->GetActivity()['id'])->delete();
                        return response()->json(['message' => 'Accept'],202);
                    case 'Join':
                        Members::where('actor', $activity['actor'])->where('object', $this->user->GetActivity()['id'])->delete();
                        return response()->json(['message' => 'Accept'],202);
                    default:
                        Log::info(print_r($activity,1));
                        Log::info('Unknown activity type: (accept)' . $activity['type'] . '/' . $activity["object"]["type"]);
                        return response()->json(['message' => 'Unknow activity '.$activity['type'] . '/' . $activity["object"]["type"]],202);
                }
            }
            case 'Create':
            {
                // Comprobamos que el actor es el mismo que attributedTo
                if (is_string($activity['object']['attributedTo']))
                    $at=$activity['object']['attributedTo'];
                else
                    $at=$activity['object']['attributedTo']['id'];
                if ($at!=$activity['actor'])
                {
                    Log::error("Actor $activity[actor] es distinto a  attributedTo $at");
                    return response()->json(['error' => 'actor y attributedTo distintos'],401);
                }

                // La guardamos, falta procesarla para discover
                // Ver si está ya en cache nos puede servir para saber si ya está procesada, ya que una misma actividad puede llegar varias veces a distintos actores subscritos
                Cache::put($activity["object"]['id'],$activity["object"],3600*24*30);
                                    
                // Compruebo si el actor está entre los seguidos del usuario
                
                $seguido=Apfollowing::where('object', $activity['actor'])->where('actor', $this->user->GetActivity()['id'])->first();
                if (is_null($seguido))
                {
                   $object=$activity['object']['inReplyTo'];
                   $reply=$activity['object'];
                   if (is_array($object)) $object=$object['id'];
                   if (is_array($reply)) $reply=$reply['id'];
                   Reply::firstOrCreate([
                       'object'=>$object,
                       'reply'=>$reply
                   ]);
                   // ############## xxxxxxxxxxxxxxx esto es solo para debug
                   $tmp=$this->GetReplys($object);
                }
                
                if ($seguido)
                {
                    Log::info("es seguido");
                    // un create de un actor a el que seguimos lo incluimos en el timeline siempre, si después la actividad es erronea o lo que sea lo vermos después
                    if ( $activity["object"]["attributedTo"] != $activity['actor'] )
                    {
                        Log::error(" distinto actor y attributedTo ".$activity["object"]["attributedTo"] . ' ' . $activity['actor'] );
                        return response()->json(['message' => 'Bad Request'],400);
                    }
                    $line= new Timeline();
                    $line->user=$this->user->GetActivity()['id'];
                    $line->actor_id=$activity['actor'];
                    $line->activity=$activity["object"]['id'];
                }
                return response()->json(['message' => 'Accept'],202);
            }
            case 'Announce':
            {
                $seguido=Apfollowing::where('object', $activity['actor'])->where('actor', $this->user->GetActivity()['id'])->first();
                if ($seguido)
                {
                    $line= new Timeline();
                    $line->user=$this->user->GetActivity()['id'];
                    $line->activity=$activity['id'];
                    $line->actor_id=$activity['actor'];
                    Cache::put($activity['id'],$activity,3600*30);
                    $line->save();
                }
                if (is_array($activity['object']))
                    $id=$activity['object']['id'];
                else
                    $id=$activity['object'];    
                if ($this->IsLocal($id))
                {
                    Announce::firstOrCreate(['actor'=>$activity['actor'],'object'=>$activity['object']]);
                }
                #Log::info("Announce: ".print_r($activity,1));
                return response()->json(['message' => 'Accept'],202);
            }
            case 'Like':
            {
                Like::firstOrCreate(['actor'=>$activity['actor'],'object'=>$activity['object']]);
                Log::info('Petición de Like ok');
                return response()->json(['message' => 'OK'],200);
            }
            case 'Block':
            {
                Log::info('Recibo Block');
                Block::firstOrCreate(['actor'=>$activity['actor'],'object'=>$activity['object']]);
                return response()->json(['message' => 'OK'],200);
            }
            case 'Delete':
            {
                // se puede borra un objeto o un actor, y el actor implica más operaciones que simplemente marcarlo como Tombstone
                Cache::put($activity['object'],['type'=>'Tombstone'],3600*24*30);
                if ($activity['actor']==$activity['object'])
                {
                    TimeLine::where('activity', $activity['object'])->where('actor_id',$activity['actor'])->delete();
                    // hay que eliminar followers y followings, blocks, members, Announces, likes, 
                    return response()->json(['message' => 'Accepted'],202);
                }
                // Para borrar no necesito el contenido del objeto, solo el id
                if (is_array($activity['object'])) $activity['object']=$activity['object']['id'];
                Cache::put($activity['object'],['type'=>'Tombstone'],3600*24*30);
                Timeline::where('activity', $activity['object'])->where('actor_id',$activity['actor'])->delete();
                Reply::where('object',$activity['object'])->orWhere('reply',$activity['object'])->delete();
                // hay que borrar nuestros likes, members y announces a este objeto                
                return response()->json(['message' => 'Accepted'],202);
                #Log::info('Petición de Delete '.print_r($activity,1));
            }
            case 'Update':
                if (isset($activity['object']['id']))
                {
                    Cache::forget($activity['object']['id']);
                    Cache::put($activity["object"]['id'],$activity["object"],3600*24*30);
                }
                return response()->json(['message' => 'Ok'],200);
            case 'Invite':
                Member::create([
                    'actor'=>$activity['actor'],
                    'object'=>$activity['object'],
                    'status'=>'Invite'
                ]);
                return response()->json(['message' => 'OK'],200);
            case 'Join':
                Member::create([
                    'actor'=>$activity['object'],
                    'object'=>$activity['actor'],
                    'status'=>'Join'
                ]);
                return response()->json(['message' => 'OK'],200);

            default:
                Log::info('Unknown activity type root: ' . $activity['type']);
                Log::info(print_r($activity,1));
                Log::error("esto no es normal234323");
                #throw new \Exception('no es normal');
                return response()->json(['message' => 'Unknow activity '.$activity['type']],501);                
        }
        Log::info('fin Inbox');
        Log::info('Aquí no deberíamos llegar nunca, debemos devolver siempre una respuesta http');
        return true;
    }
    
    public function GetReplys($id)
    {
        // xxxxxxxxxxxxxxxxxxxxxxx
        // dato el id de un objeto, saca todas las replies
        // tiene que mirar en la colección y comparar con nuestra base de datos
        $list=Reply::where('object',$id)->orderBy('id','desc')->get();
        $idsr=[];
        foreach ($list as $v)
        {
            $idsr[]=$v->reply;
            #Log::info(print_R($v,1));
        }
        $object=$this->GetObjectByUrl($id);
        if (!(isset($object['replies']))) return [];
        $object=$object['replies'];
        if (is_array($object)) $object=$object['id'];
        $listb=$this->GetListCollection($object);
        $listb=array_reverse($listb);
        $sincronizado=true;
        foreach ($listb as $v)
        {   
            if (is_array($v)) $v=$v['id'];
            if (!in_array($v,$idsr))
            {
                $sincronizado=false;
                Log::info("Añado a colección de replys $v de $id que no lo teníamos");
                Reply::create([
                  'object'=>$id,  
                  'reply'=>$v
                ]);
            }
        }
        if (!$sincronizado)
        {
            $list=Reply::where('object',$id)->orderBy('id','desc')->get();
            $new=[];
            foreach ($list as $v)
            {
                $new[]=$v->reply;
            }
            $list=$new;
        }
        return $idsr;
    }



    public function EnviarActividadPOST($json,$inbox)
    {
        if(!\p3k\url\is_url($inbox)) return false; 
        $headers = HTTPSignature::sign($this->user, $json, $inbox);
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

    public function GetUrlFirmado($url)
    {
        if(!\p3k\url\is_url($url)) return false; 
        if (!($this->user))
        {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'patalata.net');
            $date = new DateTime('UTC');
            $headers = [
                'Accept' => 'application/activity+json, application/ld+json, application/json' ,
                'Content-Type' => 'application/json',
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
        }
        else
        {
	        $headers = HTTPSignature::sign($this->user, false, $url);
	        #print_R($headers);
	        $ch = curl_init($url);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	        curl_setopt($ch, CURLOPT_USERAGENT, 'patalata.net');
	        curl_setopt($ch, CURLOPT_HEADER, true);
	        $response = curl_exec($ch);
        }
        if (curl_errno($ch)) {
                Log::info('error curl',[curl_errno($ch),curl_error($ch)]);
                $codigo=curl_getinfo($ch, CURLINFO_HTTP_CODE);
                return ['error'=>curl_error($ch),'errorhttp'=>$codigo,'errorcurl'=>curl_errno($ch)];
        }
        curl_close($ch);
        list($responseHeaders, $responseBody) = explode("\r\n\r\n", $response, 2);
        $codigo=curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res=json_decode($responseBody,1);
        if (is_array($res))
        {
            if (!(array_is_list($res)))
                $res['codhttp']=$codigo;
            return $res;
        }
        return ['error'=>"$url no es un application/activity+json",'res'=>$responseBody,'codhttp'=>'8010'];
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

    public function like($id)
    {
        $actor=$this->user->GetActivity()['id'];
        $obj=$this->GetObjectByUrl($id);
        if (isset($obj['error']))
        {
            Log::info("error: ".print_r($obj,1));
            return false;
        }
        $activity=[
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => route('activitypub.actor', ['slug' => $this->user->slug]).'/'.$id,
            'type' => 'Like',
            'actor' => $actor,
            'object' => $id
        ];
        $activity=json_encode($activity);
        $response=$this->EnviarActividadPOST($activity,$obj['inbox']);
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

