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
        $formatos=["mes"=>"Mensual","semana"=>"Semanal","dia"=>"Diario"];
        $formato="mes";

        if (request()->has('formato')) {
            $formato= request('formato');
            if (!(isset($formatos[$formato])))
            {
                $formato="mes";
            }
        }
        $formato="mes";
        if ($formato=="mes")
        {
            $desde=now()->startOfMonth();
            $hasta=now()->endOfMonth();
        }
        $eventos = Evento::whereBetween('fecha_inicio',[$desde,$hasta])->get()->sortBy('fecha_inicio'); // Obtener todos los eventos
        $evento=Evento::where('fecha_inicio','>=',now())->orderBy('fecha_inicio')->first();
        // que tengoa una imagen
        $evento=Evento::where('fecha_inicio','>=',now())->whereNotNull('cover')->orderBy('fecha_inicio')->first();
        // Obtener todos los eventos con cover
        $eventostodos = Evento::whereNotNull('cover')->get();
        return view('eventos.index', compact('evento','eventos','eventostodos','formatos','formato'));
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
            'categories' => 'nullable|array',
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
            'categories' => $request->categories,
        ]);
    
        return redirect()->route('eventos.show', $evento->slug);
    }
        

}
