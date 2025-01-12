<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Nota;
use App\Models\Post;
use App\Models\Apfollower;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;

use App\ActivityPub\HTTPSignature;
use App\ActivityPub\ActivityPub;
use Request as Rq;
use App\Traits\ModelFedi;


class ActivityPubUserController extends Controller
{

    use ModelFedi;

    public function getActor($slug): JsonResponse
    {
        // Busca al usuario por su slug
        $user = User::where('slug', $slug)->firstOrFail();
        $actor= $user->GetActivity();
        return response()->json($actor, 200, ['Content-Type' => 'application/activity+json']);
    }

    public function inbox(Request $request, $slug): JsonResponse
    {
        // Busca al usuario por su slug
        $user = User::where('slug', $slug)->firstOrFail();
        $path='/ap/users/'.$user->slug.'/inbox';
        $activity = $request->json()->all();
        if (!$this->verifySignature($user,$activity,$path)) 
        {
            return response()->json(['error' => 'Invalid signature'], 401);
        }
        return ActivityPub::InBox($user,$activity);
    }

    public function followingOLD($slug): JsonResponse
    {
        $user = User::where('slug', $slug)->firstOrFail();
        $followers = Apfollower::where('actor', $user->GetActivity()['id'])->get();
        $list=[];
        foreach ($followers as $follower)
            $list[]=$follower->object;
        $following = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => route('activitypub.following', ['slug' => $user->slug]),
            'type' => 'Collection',
            'totalItems' => count($list),
            'orderedItems' => $list
        ];
        return response()->json($following, 200, ['Content-Type' => 'application/activity+json']);
    }


    public function following($slug): JsonResponse
    {
        $user = User::where('slug', $slug)->firstOrFail();
        $listado=Apfollower::where('actor', $user->GetActivity()['id']);
        $url=route('activitypub.following', ['slug' => $user->slug]);
        return $this->Collection($listado,$url);
    }


    private function verifySignature($user,$activity,$path): bool
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
        $actor=ActivityPub::GetActorByUrl($user,$url);
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
        $user = User::where('slug', $slug)->firstOrFail();
        $notas = Nota::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subYear())
            ->orderBy('created_at', 'desc')
            ->get();
        $posts = Post::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subYear())
            ->orderBy('created_at', 'desc')
            ->get();
        $list=[];
        foreach ($posts as $activity)
        {
            $a=$activity->GetActivity();
            $list[]=$a;
        }
        foreach ($notas as $activity)
        {
            $a=$activity->GetActivity();
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

