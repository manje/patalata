<?php
namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Category;
use App\Models\Place;
use App\Models\Member;
use App\Models\Team;

use App\ActivityPub\ActivityPub;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::latest()->paginate(10);
        return view('campaigns.index', compact('campaigns'));
    }

    public function show(Request $request, $slug)
    {
        $userap=ActivityPub::GetIdentidad();
        $id=explode('@',$slug);
        if (count($id)==2)
        {
            if (parse_url($id[1], PHP_URL_HOST) == parse_url(env('APP_URL'), PHP_URL_HOST))
            {
                $slug=$id[1];
                $modelo = Campaign::where('slug', $slug)->firstOrFail();
                $campaign = $modelo->GetActivity();
            }
            else
            {
                $campaign = ActivityPub::GetActorByUsername($userap, $slug);
                if ($campaign['type']!="Group") return response()->json('Usuario no encontrado', 404);
                if (!($act['campaign'] ?? false)) return response()->json('Usuario no encontrado', 404);
                $modelo=false;
            }
        }
        else
        {
            $modelo = Campaign::where('slug', $slug)->firstOrFail();
            $campaign = $modelo->GetActivity();
        }

        if ($campaign['type']!="Group") return response()->json('Usuario no encontrado', 404);
        $c=($this->type=='Campaign')?true:false;
        if (!$c) return response()->json('Usuario no encontrado', 404);

        // hay que ver aqui si somos miembros, invitados, etc. de esta campaña y ver el $rol que tenemos, y federarlo
        if ($request->wantsJson())
            return response()->json($campaign);

        $members=[];
        if (isset($campaign['members']))
        {
            Log::info('members');
            $list=ActivityPub::GetColeccion($userap,$campaign['members']);
            if (is_array($list))
            {
                Log::info('array');
                foreach ($list as $url)
                {
                    $actor=ActivityPub::GetActorByUrl($userap,$url);
                    if (is_array($actor))
                        if(isset($actor['userfediverso']))
                            $members[]= $actor;
                }
            }
        }
        #Log::info(print_r($members, true));
        if ($modelo)
        {
            $user=Auth::user();
            $rol=$modelo->Rol($user);
        }
        else
            $rol=null;
        if ($rol=='admin')
        {
            
        }
        $local=ActivityPub::IsLocal($campaign['id']);
        return view('campaigns.show', compact('campaign','rol','local','members'));
    }

    public function create()
    {
        if (auth()->guest()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user && !is_null($user->allTeams())) {
            $equipos = $user->allTeams(); // Obtener equipos del usuario
        } else {
            return view('campaigns.error', [
                'error' => 'No puedes crear ninguna campaña porque no perteneces a ningún equipo'
            ]);
        }

        try {
            $places = Place::all();
            $categories = Category::all();
        } catch (\Exception $e) {
            Log::error('Error fetching data: ' . $e->getMessage());
            return view('campaigns.error', [
                'error' => 'Error al obtener datos necesarios para crear una campaña'
            ]);
        }
        $campaign=new Campaign();
        return view('campaigns.create', compact('places', 'categories', 'equipos', 'campaign'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'summary' => 'required|string|max:2048',
            'content' => 'required|string|max:10000',
            'place_id' => 'nullable|exists:places,id',
            'team_id' => 'required|exists:teams,id',
            'categories' => 'nullable|array', // El nombre del campo es 'categorias'
            'categories.*' => 'exists:categories,id', // Verifica que cada categoría seleccionada exista
            'profile_image' => 'required|string',
            'image' => 'string',
            'slug' => ['required', 'string', 'max:25', 'unique:users', 'unique:teams', 'unique:campaigns', 'regex:/^[a-z0-9_]+$/'],
        ],
        [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no debe exceder los 250 caracteres.',
            'summary.required' => 'El resumen es obligatorio.',
            'summary.max' => 'El resumen no debe exceder los 2000 caracteres.',
            'content.required' => 'El contenido de la campaña es obligatorio.',
            'place_id.exists' => 'El lugar seleccionado no existe.',
            'team_id.required' => 'El equipo es obligatorio.',
            'team_id.exists' => 'El equipo seleccionado no existe.',
            'slug.required' => 'El nombre de usuario de la campaña es obligatorio.',
            'slug.max' => 'El nombre de usuario no debe exceder los 25 caracteres.',
            'slug.unique' => 'El nombre de usuario ya está en uso.',
            'slug.regex' => 'El nombre de usuario debe contener solo letras, números y guiones bajos.',
            'profile_image.required' => 'La imagen de perfil es obligatoria.',
            'categories.*.exists' => 'Al menos una categoría seleccionada no existe.',
        ]
        
        );

        $dir = now()->format('Y-m');

        $destinationDir = "campaigns/$dir";

        // Verifica si el directorio existe, si no, lo crea
        if (!Storage::disk('public')->exists($destinationDir)) {
            Storage::disk('public')->makeDirectory($destinationDir);
        }


        if ($request->profile_image)
        {
            Storage::disk('public')->move($request->profile_image, "campaigns/$dir/".basename($request->profile_image));
            $request->profile_image="campaigns/$dir/".basename($request->profile_image);
        }
        if ($request->image) {
            Storage::disk('public')->move($request->image, "campaigns/$dir/".basename($request->image));
            $request->image="campaigns/$dir/".basename($request->image);
        } 

        $campaign = Campaign::create([
            'team_id' => $request->team_id,
            'profile_image'=>$request->profile_image,
            'image'=> $request->image,
            'name' => $request->name,
            'summary' => $request->summary,
            'content' => $request->content,
            'place_id' => $request->place_id,
            'slug' => $request->slug,
        ]);

        if ($request->has('categorias')) {
            $campaign->categories()->sync($request->categories);
        }
        $team=Team::find($request->team_id);
        Member::create([
            'actor' => $campaign->GetActivity()['id'],
            'object' => $team->GetActivity()['id'],
            'status' => 'admin'
        ]);
        Session::forget('temp_image_profile_image');
        Session::forget('temp_image_image');

        return redirect()->route('campaigns.show', $campaign->slug);
    }

    public function edit(Request $request, $slug)
    {
        $campaign = Campaign::where('slug', $slug)->firstOrFail();
        $places = Place::all();
        $categories = Category::all();
        return view('campaigns.edit', compact('campaign', 'places', 'categories'));
    }

    public function update(Request $request, $slug)
    {
        Log::info($request->summary);
        $request->validate([
            'name' => ['required', 'string', 'max:250'],
            'summary' => ['required', 'string', 'max:2000'],
            'content' => ['required', 'string'],
            'place_id' => 'nullable|exists:places,id',
            'categories' => 'nullable|array', // El nombre del campo es 'categorias'
            'categories.*' => 'exists:categories,id', // Verifica que cada categoría seleccionada exista
            'profile_image' => 'required|string',
            'image' => 'string'
        ],
        [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no debe exceder los 250 caracteres.',
            'summary.required' => 'El resumen es obligatorio.',
            'summary.max' => 'El resumen no debe exceder los 2000 caracteres.',
            'content.required' => 'El contenido de la campaña es obligatorio.',
            'place_id.exists' => 'El lugar seleccionado no existe.',
            'categories.*.exists' => 'Al menos una categoría seleccionada no existe.',
        ]
        
        );

        $campaign=Campaign::where('slug', $slug)->firstOrFail();

        $dir = now()->format('Y-m');

        if ($request->profile_image!=$campaign->profile_image)
        {
            if ($campaign->profile_image)
                Storage::disk('public')->delete($campaign->profile_image);
            Storage::disk('public')->move($request->profile_image, "campaigns/$dir/".basename($request->profile_image));
            $request->profile_image="campaigns/$dir/".basename($request->profile_image);
        }
        if ($request->image!=$campaign->image) {
            if ($campaign->image)
                Storage::disk('public')->delete($campaign->image);
            Storage::disk('public')->move($request->image, "campaigns/$dir/".basename($request->image));
            $request->image="campaigns/$dir/".basename($request->image);
        }





        $campaign->update([
            'profile_image'=>$request->profile_image,
            'image'=> $request->image,
            'name' => $request->name,
            'summary' => $request->summary,
            'content' => $request->content,
            'place_id' => $request->place_id,
        ]);

        if ($request->has('categorias')) {
            $campaign->categories()->sync($request->categories);
        }
        Session::forget('temp_image_profile_image');
        Session::forget('temp_image_image');

        return redirect()->route('campaigns.show', $campaign->slug);

        
    }
}
