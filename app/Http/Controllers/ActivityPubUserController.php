<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
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
        Log::info('key public: ' . $user->public_key);

        // Construye el objeto Actor en formato JSON-LD
        $actor = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => route('activitypub.actor', ['slug' => $user->slug]),
            'type' => 'Person',
            'preferredUsername' => $user->slug,
            'name' => $user->name,
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


    public function inbox(Request $request, $slug)
    {
        // Busca al usuario por su slug
        $user = User::where('slug', $slug)->firstOrFail();
        $path='/ap/users/'.$user->slug.'/inbox';

        
        $activity = $request->json()->all();
        Log::info('actividad recibida: ' . $activity['type']);
        
        Log::info('actor: ' . $activity['actor']);
        // Verifica la firma
        if (!$this->verifySignature($activity,$path)) {
            Log::error('Invalid signature');
            return response()->json(['error' => 'Invalid signature'], 400);
        }
        else
            Log::info('Valid signature');
        // Aquí puedes agregar lógica para manejar las actividades recibidas
        // Ejemplo: Si es un 'Follow', registrar al usuario que sigue a otro.
        
        if ($activity['type'] === 'Follow') {
            // Ejemplo: Registrar la relación de seguidor

        }


        ActivityPub::InBox($user,$activity);


        return response()->json(['status' => 'ok'], 200);
    }



private function verifySignature($activity,$path): bool
{
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
    $response = Http::withHeaders([
        'Accept' => 'application/activity+json',
    ])->get($url);
    $actor = $response->json();
    if (!(isset($actor["publicKey"])))
        return false;

    $publicKey=$actor["publicKey"];
    $inputHeaders = Rq::instance()->headers->all();

    list($verified, $headers) = HTTPSignature::verify($publicKey['publicKeyPem'], $signatureData, $inputHeaders, $path, $body);
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
        $posts = Post::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subYear())
            ->orderBy('created_at', 'desc')
            ->get();


        // Construye el contenedor del Outbox
        $outbox = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => route('activitypub.outbox', ['slug' => $user->slug]),
            'type' => 'OrderedCollection',
            'totalItems' => $posts->count(),
            'orderedItems' => $posts->map(function ($post) use ($user) {
                return [
                    'id' => route('posts.show', ['slug' => $post->slug]),
                    'type' => 'Note',
                    'published' => $post->created_at->toIso8601String(),
                    'attributedTo' => route('activitypub.actor', ['slug' => $user->slug]),
                    'content' => $post->content,
                    'url' => route('posts.show', ['slug' => $post->slug]),
                ];
            }),
        ];
        // Devuelve el Outbox en formato JSON-LD
        return response()->json($outbox, 200, ['Content-Type' => 'application/activity+json']);
    }






}

