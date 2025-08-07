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
use App\ActivityPub\LDSignature;

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
        $signatureData = HTTPSignature::parseSignatureHeader(Rq::header('signature'));
        // Busca al usuario por su slug
        $user=ActivityPub::GetIdentidadBySlug($slug);
        $ap=new ActivityPub($user);
        $path='/ap/users/'.$user->slug.'/inbox';
        $activity = $request->json()->all();
        if (!$this->verifySignature($activity,$path))
        {
            if (!isset($activity['signature']))
                return response()->json(['error' => 'Invalid signature'], 401);
            $sig=new LDSignature();
            $actor=$ap->GetActorByUrl($user,$activity['actor']);
            if (!(isset($actor['publicKey'])))
            {
                return response()->json(['error' => 'Invalid signature'], 401);
            }
            $res=$sig->verify($activity,$actor['publicKey']['publicKeyPem']);
            if ($res)
            {
                Log::info("Valida\tsignature LD t $activity[type] $actor[id] ");
                return $ap->InBox($user,$activity);
            }
            Log::info("Inválida\tsignature LD $activity[type] $actor[id] ");
            return response()->json(['error' => 'Invalid signature'], 401);
        }
        Log::info("Válida signature $path ".$user->slug." ".$signatureData['algorithm']);
        return $ap->InBox($activity);
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


    private function verifySignature($activity,$path): bool
    {
        $ap=new ActivityPub(null);
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
        $actor=$ap->GetActorByUrl($url);
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

// copiado de chatgpt para verificar la firma "interna"
function verifySignature(array $activity)
{
    if (!isset($activity['signature'])) {
        throw new Exception("No signature found in the activity.");
    }

    $signature = $activity['signature'];
    if (!isset($signature['signatureValue'], $signature['creator'])) {
        throw new Exception("Signature structure is invalid.");
    }

    $signedObject = json_encode($activity['object'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $signatureValue = base64_decode($signature['signatureValue']);
    $creatorUrl = $signature['creator'];

    // Obtener la clave pública del actor (suponiendo que el actor tiene la clave en su perfil)
    $publicKey = fetchPublicKey($creatorUrl);
    if (!$publicKey) {
        throw new Exception("Failed to retrieve the public key.");
    }

    // Verificar la firma usando la clave pública
    $verified = openssl_verify($signedObject, $signatureValue, $publicKey, OPENSSL_ALGO_SHA256);

    return $verified === 1;
}

function fetchPublicKey(string $actorUrl)
{
    $actorData = Activitypub::GetActorByUrl($actorUrl);
    if (!$actorData) {
        return false;
    }

    $actor = json_decode($actorData, true);
    if (!isset($actor['publicKey']['publicKeyPem'])) {
        return false;
    }

    return $actor['publicKey']['publicKeyPem'];
}

// Ejemplo de uso
try {
    $activity = json_decode(file_get_contents('php://input'), true);
    $isValid = verifySignature($activity);

    if ($isValid) {
        echo "Firma válida.";
    } else {
        echo "Firma no válida.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}


>>>>>>> b2aa73cd2253906bc780fc727af928ebbc0b777e





}

