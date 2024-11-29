<?php
namespace App\Http\Controllers;

use App\Models\Nota;
use App\Models\Municipio;
use App\Models\Category;
use App\Models\NotaFile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotaController extends Controller
{
    public function index()
    {
        $notas = Nota::latest()->paginate(10);
        return view('notas.index', compact('notas'));
    }

    public function show(Request $request, $slug)
    {   
        // busco por id que es más rápido
        $nota = Nota::with('equipo', 'creador','notaFiles')->where('id', (int)$slug)->firstOrFail();
        if ($request->wantsJson()) {
            return response()->json($nota->GetActivity());
        }
        return view('notas.show', compact('nota'));
    }

    public function create()
    {
    	// comprobamos por si no está registrado 
   		if (auth()->guest())
		{
			return redirect()->route('login');
		}
        // vemos si AllTemas es nulo
        if (is_null(auth()->user()->allTeams())==false) {
            $equipos = auth()->user()->allTeams(); // Obtener equipos del usuario
        } else {
            $equipos = [];
        }

        $municipios = Municipio::all();
        $categories = Category::all();
        return view('notas.create', compact('municipios', 'categories', 'equipos'));
	
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:255',
            'municipio_id' => 'required|exists:municipios,id',
            'team_id' => 'nullable|exists:teams,id',
            'cover' => 'nullable|image|max:4096',
            'categories' => 'nullable|array', // El nombre del campo es 'categorias'
            'categories.*' => 'exists:categories,id', // Verifica que cada categoría seleccionada exista
        ]);
        Log::info($request->all());
        Log::info($request->content);
        $cta = $request->content;
        $dir = now()->format('Y-m');

        $nota = Nota::create([
            'user_id' => auth()->id(),
            'content' => $cta,
            'team_id' => $request->team_id,
            'municipio_id' => $request->municipio_id,
            'cover' => $request->file('cover') ? $request->file('cover')->store('nota/'.$dir,"public") : null,
        ]);

        if ($request->has('categorias')) {
            $nota->categories()->sync($request->categories);
        }

  		// Procesar cada archivo recibido en el formulario
	    if ($request->has('archivos')) {
	        foreach ($request->file('archivos') as $file) {

	            // Determinar el tipo de archivo (imagen, video, audio)
	            $type = $file->getMimeType();

	            // continue si no es imagen, ni video, ni audio
	            if (!str_starts_with($type, 'image/') && !str_starts_with($type, 'video/') && !str_starts_with($type, 'audio/')) 
	                continue;

	            // Guardar el archivo en el almacenamiento y obtener la ruta
	            $path = $file->store('nota/'.$dir, 'public');

	            // Crear una entrada en notafiles
	            NotaFile::create([
	                'nota_id' => $nota->id,
	                'file_path' => $path,
	                'file_type' => $type,
	            ]);
	        }
	    }

        return redirect()->route('notas.show', $nota->slug);
    }
}
