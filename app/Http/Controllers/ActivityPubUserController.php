<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Nota;
use App\Models\Post;
use App\Models\Apfollower;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation;

#use ActivityPhp\Server;
#use ActivityPhp\Server\Http\HttpSignature;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;

use App\ActivityPub\HTTPSignature;
use App\ActivityPub\ActivityPub;
use Request as Rq;


class ActivityPubUserController extends Controller
{
    public function getActor($slug): JsonResponse
    {
        // Busca al usuario por su slug
        Log::info("getActor: " . $slug);
        $user = User::where('slug', $slug)->firstOrFail();
        $actor = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => route('activitypub.actor', ['slug' => $user->slug]),
            'type' => 'Person',
            'preferredUsername' => $user->slug,
            'name' => $user->name,
            'inbox' => route('activitypub.inbox', ['slug' => $user->slug]),
            'outbox' => route('activitypub.outbox', ['slug' => $user->slug]),
            'following' => route('activitypub.following', ['slug' => $user->slug]),
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
        return response()->json($actor, 200, ['Content-Type' => 'application/activity+json']);
    }


    public function inbox(Request $request, $slug): JsonResponse
    {
        // Busca al usuario por su slug
        $user = User::where('slug', $slug)->firstOrFail();
        $path='/ap/users/'.$user->slug.'/inbox';
        $activity = $request->json()->all();
        Log::info('actividad recibida: ' . $activity['type']);
        Log::info('actor: ' . $activity['actor']);
        // Verifica la firma
        if (!$this->verifySignature($user,$activity,$path)) {
            Log::error('Invalid signature');
            return response()->json(['error' => 'Invalid signature'], 400);
        }
        ActivityPub::InBox($user,$activity);
        return response()->json(['status' => 'ok'], 200);
    }

    public function following($slug): JsonResponse
    {
        // Busca al usuario por su slug
        $user = User::where('slug', $slug)->firstOrFail();
        $followers = Apfollower::where('user_id', $user->id)->get();
        $list=[];
        foreach ($followers as $follower)
            $list[]=$follower->actor_id;
        $following = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => route('activitypub.following', ['slug' => $user->slug]),
            'type' => 'Collection',
            'totalItems' => count($list),
            'orderedItems' => $list
        ];
        return response()->json($following, 200, ['Content-Type' => 'application/activity+json']);
    }





private function verifySignature($user,$activity,$path): bool
{
    Log::info('verifySignature');
    if(!Rq::header('signature')) {
      return response()->json([
        'error' => 'Missing Signature header'
      ], 400);
    }
    $body = Rq::instance()->getContent();
    $signatureData = HTTPSignature::parseSignatureHeader(Rq::header('signature'));
    if(isset($signatureData['error']))
        return false;


    $url=$activity['actor'];
    $actor=ActivityPub::GetUrlFirmado($user,$url);

    if (!(isset($actor["publicKey"])))
        return false;

    $publicKey=$actor["publicKey"];
    $inputHeaders = Rq::instance()->headers->all();
    list($verified, $headers) = HTTPSignature::verify($publicKey['publicKeyPem'], $signatureData, $inputHeaders, $path, $body);
    if (!($verified==1))
    {
        Log::info('verifySignature: ' . $publicKey['publicKeyPem']);
        Log::info('signatureData: ' . print_r($signatureData, true));
        Log::info('inputHeaders: ' . print_r($inputHeaders, true));
        Log::info('path: ' . $path);
        Log::info('body: ' . $body);
    }

    return ($verified==1);
}

    



    public function webFinger(Request $request)
    {
        $resource = $request->query('resource');

        // Verifica que el recurso sea un handle válido
        if (!str_starts_with($resource, 'acct:')) {
            return response()->json(['error' => 'Invalid resource format'], 400);
        }

        // Extrae el handle (slug y dominio)
        $handle = substr($resource, 5); // Remueve 'acct:'
        [$slug, $domain] = explode('@', $handle);

        // Asegúrate de que el dominio sea el tuyo
        if ($domain !== parse_url(config('app.url'), PHP_URL_HOST)) {
            return response()->json(['error' => 'Domain mismatch'], 404);
        }

        // Busca al usuario por su slug
        $user = User::where('slug', $slug)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Construye la respuesta WebFinger
        $webfinger = [
            'subject' => "acct:{$user->slug}@{$domain}",
            'links' => [
                [
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => route('activitypub.actor', ['slug' => $user->slug]),
                ],
            ],
        ];

        return response()->json($webfinger, 200, ['Content-Type' => 'application/jrd+json']);
    }






    public function outbox($slug): JsonResponse
    {
        Log::info('Nueva petición de salida en el buzón de ' . $slug);
        // hago log con los datos de la petición
        Log::info(request()->all());


        // Busca al usuario por su slug
        $user = User::where('slug', $slug)->firstOrFail();

        // Obtiene los artículos publicados por el usuario (último año)
        $notas = Nota::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subYear())
            ->orderBy('created_at', 'desc')
            ->get();
        $posts = Post::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subYear())
            ->orderBy('created_at', 'desc')
            ->get();
        Log::info('Notas: ' . $notas->count());
        Log::info('Posts: ' . $posts->count());

        // Construyo un array mezclando notas y posts y ordenándolos por updated_at
        
        $list=[];
        foreach ($posts as $activity)
        {
            $a=$activity->GetActivity();
            Log::info('Actividad: ' . print_r($a['id'],1));
            $list[]=$a;
        }
        foreach ($notas as $activity)
        {
            $a=$activity->GetActivity();
            Log::info('Actividad: ' . print_r($a['id'],1));
            $list[]=$a;
        }
        // ordeno $activity por updated
        usort($list, function($a, $b) {
            return $b['updated'] <=> $a['updated'];
        });

        // Construye el contenedor del Outbox
        $outbox = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => route('activitypub.outbox', ['slug' => $user->slug]),
            'type' => 'OrderedCollection',
            'totalItems' => count($list),
            'orderedItems' => $list
        ];
        return response()->json($outbox, 200, ['Content-Type' => 'application/activity+json']);
    }

}
