<?php
    
namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use App\Models\Municipio;
use App\Models\EventType;
use App\Models\Category;

use Illuminate\Support\Facades\Log;


class EventoController extends Controller
{
    /**
     * Muestra la lista de eventos.
     *
     * @return \Illuminate\View\View
     */



    public function index()
    {
        if (auth()->guest())
        {
            $superior = Evento::where('fecha_inicio','>=',now())->whereNotNull('cover')->orderBy('fecha_inicio')->limit(5)->get();
            Log::info($superior->count());
        }
        else
        {
            $municipio_id=auth()->user()->municipio_id;
            $provincia=auth()->user()->municipio->cpro;
            // en la parte superior eventos provinciales, si no hay 5 todos
            $superior = Evento::join('municipios', 'eventos.municipio_id', '=', 'municipios.id')
                ->where('eventos.fecha_inicio', '>=', now())
                ->where('municipios.cpro', $provincia)
                ->whereNotNull('eventos.cover')
                ->orderBy('eventos.fecha_inicio')
                ->select('eventos.*')
                ->limit(5)
                ->get();
            if ($superior->count()<5)
                $superior = Evento::where('fecha_inicio','>=',now())->whereNotNull('cover')->orderBy('fecha_inicio')->limit(5)->get();
        }
        return view('eventos.index', compact('superior'));
    }

    /**
     * Muestra el detalle de un evento.
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function show($slug)
    {
        $evento = Evento::with('equipo', 'creador')->where('slug', $slug)->firstOrFail(); // Buscar evento por slug
        return view('eventos.show', compact('evento'));
    }


    public function create(Request $request)
    {
        $municipios = Municipio::all(); // Obtener todos los municipios
        // vemos si AllTemas es nulo
        if (is_null(auth()->user()->allTeams())==false) {
            $equipos = auth()->user()->allTeams(); // Obtener equipos del usuario
        } else {
            $equipos = [];
        }
        $eventTypes=EventType::all();
        $categories=Category::all();
        if ($request->has('fecha'))
            $fecha=date("Y-m-d",strtotime($request->fecha));
        else
            $fecha=now()->format('Y-m-d');
        return view('eventos.create', compact('municipios', 'equipos','eventTypes','categories','fecha'));
    }
    
    public function store(Request $request)
    {

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'municipio_id' => 'required|exists:municipios,id',
            'team_id' => 'nullable|exists:teams,id',
            'cover' => 'nullable|image|max:4096',
            'event_type_id' => 'required|exists:event_types,id',
            'categorias' => 'nullable|array', // El nombre del campo es 'categorias'
            'categorias.*' => 'exists:categories,id', // Verifica que cada categoría seleccionada exista
        ]);  

        $dir = now()->format('Y-m');

        $evento = Evento::create([
            'user_id' => auth()->id(),
            'team_id' => $request->team_id,
            'municipio_id' => $request->municipio_id,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'cover' => $request->file('cover') ? $request->file('cover')->store('evento/'.$dir,"public") : null,
            'event_type_id' => $request->event_type_id,
        ]);


        if ($request->has('categorias')) {
            $evento->categories()->sync($request->categorias);
        }





        return redirect()->route('eventos.show', $evento->slug);
    }
        

}
