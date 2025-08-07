<?php
namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Category;
use App\Models\Place;
use App\Models\Member;
use App\Models\Team;
use App\Models\Apfile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Enums\Srid;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::latest()->paginate(50);
        return view('events.index', compact('events'));
    }

    public function show(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        Log::info($event);
        if ($request->wantsJson())
            return response()->json($event->GetActivity());
        return view('events.show', compact('event'));
    }

    public function create()
    {
        if (auth()->guest()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user && !is_null($user->allTeams())) 
            $equipos = $user->allTeams(); // Obtener equipos del usuario
        else
            $equipos = [];

        try {
            $places = Place::all();
            $categories = Category::all();
        } catch (\Exception $e) {
            Log::error('Error fetching data: ' . $e->getMessage());
            return view('events.error', [
                'error' => 'Error al obtener datos necesarios para crear una campaña'
            ]);
        }
        $event=new Event();
        $uniqid=false;
        $event->uniqid=$uniqid;
        return view('events.create', compact('places', 'categories', 'equipos', 'event','uniqid'));
    }

    public function store(Request $request)
    {
        Log::info($request->all());
        $request->validate([
            'uniqid' => 'string',
            'name' => 'required|string|max:255',
            'summary' => 'required|string|max:2048',
            'content' => 'nullable|string|max:10000',
            'place_id' => 'nullable|exists:places,id',
            'team_id' => 'nullable|exists:teams,id',
            'startTime' => 'required|date_format:Y-m-d\TH:i',
            'endTime' => 'nullable|date_format:Y-m-d\TH:i|after:startTime',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',            
            'location_name' => 'required|string|max:250',
            'location_addressCountry' => 'required|string|max:250',
            'location_addressLocality' => 'required|string|max:250',
            'location_addressRegion' => 'required|string|max:250',
            'location_postalCode' => 'required|string|max:250',
            'location_streetAddress' => 'required|string|max:250',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ],
        [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no debe exceder los 255 caracteres.',
            'summary.required' => 'El resumen es obligatorio.',
            'summary.max' => 'El resumen no debe exceder los 2048 caracteres.',
            'content.max' => 'El contenido no debe exceder los 10000 caracteres.',
            'place_id.exists' => 'La localidad seleccionada no existe.',
            'team_id.exists' => 'El equipo seleccionado no existe.',
            'startTime.required' => 'La fecha y hora de inicio es obligatoria.',
            'startTime.date_format' => 'El formato de la fecha y hora de inicio no es válido.',
            'endTime.date_format' => 'El formato de la fecha y hora de finalización no es válido.',
            'endTime.after' => 'La fecha y hora de finalización debe ser posterior a la de inicio.',
            'latitude.required' => 'La latitud es obligatoria.',
            'latitude.numeric' => 'La latitud debe ser un número.',
            'latitude.between' => 'La latitud debe estar entre -90 y 90.',
            'longitude.required' => 'La longitud es obligatoria.',
            'longitude.numeric' => 'La longitud debe ser un número.',
            'longitude.between' => 'La longitud debe estar entre -180 y 180.',
            'location_name.required' => 'El nombre del lugar es obligatorio.',
            'location_name.max' => 'El nombre del lugar no debe exceder los 250 caracteres.',
            'location_addressCountry.required' => 'El país es obligatorio.',
            'location_addressCountry.max' => 'El país no debe exceder los 250 caracteres.',
            'location_addressLocality.required' => 'La localidad es obligatoria.',
            'location_addressLocality.max' => 'La localidad no debe exceder los 250 caracteres.',
            'location_addressRegion.required' => 'La región es obligatoria.',
            'location_addressRegion.max' => 'La región no debe exceder los 250 caracteres.',
            'location_postalCode.required' => 'El código postal es obligatorio.',
            'location_postalCode.max' => 'El código postal no debe exceder los 250 caracteres.',
            'location_streetAddress.required' => 'La dirección es obligatoria.',
            'location_streetAddress.max' => 'La dirección no debe exceder los 250 caracteres.',
            'categories.*.exists' => 'Al menos una categoría seleccionada no existe.',
        ]);
        
        $dir = now()->format('Y/m');
        $destinationDir = "events/$dir";

        // Verifica si el directorio existe, si no, lo crea
        if (!Storage::disk('public')->exists($destinationDir)) {
            Storage::disk('public')->makeDirectory($destinationDir);
        }


        $event = Event::create([
            'user_id' => Auth::user()->id,
            'team_id' => $request->team_id,
            'place_id' => $request->place_id,
            'name' => $request->name,
            'summary' => $request->summary,
            'content' => $request->content,
            'startTime' => $request->startTime,
            'endTime' => $request->endTime,
            'coordinates' => new Point ($request->latitude,$request->longitude),
            'location_name' => $request->location_name,
            'location_addressCountry' => $request->location_addressCountry,
            'location_addressLocality' => $request->location_addressLocality,
            'location_addressRegion' => $request->location_addressRegion,
            'location_postalCode' => $request->location_postalCode,
            'location_streetAddress' => $request->location_streetAddress,
        ]);

        if ($request->has('categorias')) {
            $event->categories()->sync($request->categories);
        }
        $files=Session::get('multimedia_tmp_'.$request->uniqid);
        foreach ($files as $file)
        {
            // primero los muevo del path actual al destinationDir
            Storage::disk('public')->move($file['path'],$destinationDir.'/'.basename($file['path']));
            Apfile::create([
                'file_path' => $destinationDir.'/'.basename($file['path']),
                'file_type' => $file['mime'],
                'alt_text' => $file['alt'],
                'apfileable_id' => $event->id,
                'apfileable_type' => 'App\Models\Event'
            ]);
        }
        return redirect()->route('events.show', $event->slug);
    }

    public function edit(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        $event->latitude=$event->coordinates->latitude;
        $event->longitude=$event->coordinates->longitude;
        $uniqid=false;
        $places = Place::all();
        $categories = Category::all();
        return view('events.edit', compact('event', 'places', 'categories','uniqid'));
    }

    public function update(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        Session::flash('uniqid', $request->uniqid);

        $request->validate([
            'uniqid' => 'string',
            'name' => 'required|string|max:255',
            'summary' => 'required|string|max:2048',
            'content' => 'nullable|string|max:10000',
            'place_id' => 'nullable|exists:places,id',
            'startTime' => 'required|date_format:Y-m-d\TH:i',
            'endTime' => 'nullable|date_format:Y-m-d\TH:i|after:startTime',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',            
            'location_name' => 'required|string|max:250',
            'location_addressCountry' => 'required|string|max:250',
            'location_addressLocality' => 'required|string|max:250',
            'location_addressRegion' => 'required|string|max:250',
            'location_postalCode' => 'required|string|max:250',
            'location_streetAddress' => 'required|string|max:250',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ],
        [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no debe exceder los 255 caracteres.',
            'summary.required' => 'El resumen es obligatorio.',
            'summary.max' => 'El resumen no debe exceder los 2048 caracteres.',
            'content.max' => 'El contenido no debe exceder los 10000 caracteres.',
            'place_id.exists' => 'La localidad seleccionada no existe.',
            'team_id.exists' => 'El equipo seleccionado no existe.',
            'startTime.required' => 'La fecha y hora de inicio es obligatoria.',
            'startTime.date_format' => 'El formato de la fecha y hora de inicio no es válido.',
            'endTime.date_format' => 'El formato de la fecha y hora de finalización no es válido.',
            'endTime.after' => 'La fecha y hora de finalización debe ser posterior a la de inicio.',
            'latitude.required' => 'La latitud es obligatoria.',
            'latitude.numeric' => 'La latitud debe ser un número.',
            'latitude.between' => 'La latitud debe estar entre -90 y 90.',
            'longitude.required' => 'La longitud es obligatoria.',
            'longitude.numeric' => 'La longitud debe ser un número.',
            'longitude.between' => 'La longitud debe estar entre -180 y 180.',
            'location_name.required' => 'El nombre del lugar es obligatorio.',
            'location_name.max' => 'El nombre del lugar no debe exceder los 250 caracteres.',
            'location_addressCountry.required' => 'El país es obligatorio.',
            'location_addressCountry.max' => 'El país no debe exceder los 250 caracteres.',
            'location_addressLocality.required' => 'La localidad es obligatoria.',
            'location_addressLocality.max' => 'La localidad no debe exceder los 250 caracteres.',
            'location_addressRegion.required' => 'La región es obligatoria.',
            'location_addressRegion.max' => 'La región no debe exceder los 250 caracteres.',
            'location_postalCode.required' => 'El código postal es obligatorio.',
            'location_postalCode.max' => 'El código postal no debe exceder los 250 caracteres.',
            'location_streetAddress.required' => 'La dirección es obligatoria.',
            'location_streetAddress.max' => 'La dirección no debe exceder los 250 caracteres.',
            'categories.*.exists' => 'Al menos una categoría seleccionada no existe.',
        ]);

        $dir = now()->format('Y/m');
        $destinationDir = "events/$dir";

        $event->name = $request->name;
        $event->summary = $request->summary;
        $event->content = $request->content;
        $event->place_id = $request->place_id;
        $event->startTime = $request->startTime;
        $event->endTime = $request->endTime;
        $event->coordinates = new Point($request->latitude, $request->longitude);
        $event->location_name = $request->location_name;
        $event->location_addressCountry = $request->location_addressCountry;
        $event->location_addressLocality = $request->location_addressLocality;
        $event->location_addressRegion = $request->location_addressRegion;
        $event->location_postalCode = $request->location_postalCode;
        $event->location_streetAddress = $request->location_streetAddress;
        $event->save();

        if ($request->has('categorias')) {
            $event->categories()->sync($request->categories);
        }
        $old=$event->apfiles;
        $files=Session::get('multimedia_tmp_'.$request->uniqid);
        foreach ($files as $file)
        {
            $existe=false;
            // comprobamos si existía
            foreach ($old as $f)
            {
                if ($f->file_path==$file['path'])
                    $existe=$f;
            }
            if ($existe)
            {
                if ($existe->alt_text!=$file['alt'])
                {
                    $existe->alt_text=$file['alt'];
                    $existe->save();
                }
            }
            else
            {
                Storage::disk('public')->move($file['path'],$destinationDir.'/'.basename($file['path']));
                Apfile::create([
                    'file_path' => $destinationDir.'/'.basename($file['path']),
                    'file_type' => $file['mime'],
                    'alt_text' => $file['alt'],
                    'apfileable_id' => $event->id,
                    'apfileable_type' => 'App\Models\Event'
                ]);
            }
        }
        foreach ($old as $f) {
            $existe = false;
            foreach ($files as $file) {
                if ($f->file_path == $file['path']) {
                    $existe = true;
                    break;
                }
            }
            if (!$existe) {
                Storage::disk('public')->delete($f->file_path);
                $f->delete();
            }
        }
        return redirect()->route('events.show', $event->slug);        
    }
}
