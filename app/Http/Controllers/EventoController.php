<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use App\Models\Municipio;

class EventoController extends Controller
{
    /**
     * Muestra la lista de eventos.
     *
     * @return \Illuminate\View\View
     */



    public function index()
    {
        $eventos = Evento::with('equipo', 'creador')->get(); // Traer todos los eventos con sus equipos o creadores

        return view('eventos.index', compact('eventos'));
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


    public function create()
    {
        $municipios = Municipio::all(); // Obtener todos los municipios
        $equipos = auth()->user()->allTeams(); // Obtener equipos del usuario
        return view('eventos.create', compact('municipios', 'equipos'));
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
        ]);
    
        $evento = Evento::create([
            'user_id' => auth()->id(),
            'team_id' => $request->team_id,
            'municipio_id' => $request->municipio_id,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'cover' => $request->file('cover') ? $request->file('cover')->store('covers',"public") : null,
        ]);
    
        return redirect()->route('eventos.index')->with('success', 'Evento creado con éxito.');
    }
        

}
