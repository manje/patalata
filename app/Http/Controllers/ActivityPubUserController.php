<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Team;
use App\Models\Nota;
use App\Models\Post;
use App\Models\Apfollower;
use App\Models\Apfollowing;
use App\Models\Member;
use App\Models\Outbox;

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
        $user=ActivityPub::GetIdentidadBySlug($slug);
        $actor= $user->GetActivity();
        return response()->json($actor, 200, ['Content-Type' => 'application/activity+json']);
    }

    public function inbox(Request $request, $slug): JsonResponse
    {
        // Busca al usuario por su slug
        $user=ActivityPub::GetIdentidadBySlug($slug);
        $path='/ap/users/'.$user->slug.'/inbox';
        $activity = $request->json()->all();
        if (!$this->verifySignature($user,$activity,$path)) 
        {
            return response()->json(['error' => 'Invalid signature'], 401);
        }
        return ActivityPub::InBox($user,$activity);
    }

    public function following($slug): JsonResponse
    {
        $user=ActivityPub::GetIdentidadBySlug($slug);
        $listado=Apfollowing::where('actor', $user->GetActivity()['id']);
        $url=route('activitypub.following', ['slug' => $user->slug]);
        return $this->Collection($listado,$url);
    }


    public function followers($slug): JsonResponse
    {
        $user=ActivityPub::GetIdentidadBySlug($slug);
        $listado=Apfollower::where('actor', $user->GetActivity()['id']);
        $url=route('activitypub.followers', ['slug' => $user->slug]);
        return $this->Collection($listado,$url);
    }

    public function members($slug): JsonResponse
    {
        $user=ActivityPub::GetIdentidadBySlug($slug);
        $listado = Member::where('actor', $user->GetActivity()['id'])
        ->whereIn('status', ['admin', 'editor']);
        $url=route('activitypub.members', ['slug' => $user->slug]);
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
        $user=ActivityPub::GetIdentidadBySlug($slug);
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
        $user=ActivityPub::GetIdentidadBySlug($slug);
        $list=Outbox::where('actor', $user->GetActivity()['id']);
        $url=route('activitypub.outbox', ['slug' => $user->slug]);
        return $this->Collection($list,$url);

    }

}

