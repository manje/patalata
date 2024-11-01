<?php
namespace App\Http\Controllers;

use App\Models\Denuncia;
use App\Models\Municipio;
use App\Models\Category;
use App\Models\DenunciaFile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DenunciaController extends Controller
{
    public function index()
    {
        $denuncias = Denuncia::latest()->paginate(10);
        return view('denuncias.index', compact('denuncias'));
    }

    public function show($slug)
    {
        $denuncia = Denuncia::with('equipo', 'creador','denunciaFiles')->where('slug', $slug)->firstOrFail();
        return view('denuncias.show', compact('denuncia'));
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
        return view('denuncias.create', compact('municipios', 'categories', 'equipos'));
	
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'municipio_id' => 'required|exists:municipios,id',
            'team_id' => 'nullable|exists:teams,id',
            'cover' => 'nullable|image|max:4096',
            'categories' => 'nullable|array', // El nombre del campo es 'categorias'
            'categories.*' => 'exists:categories,id', // Verifica que cada categoría seleccionada exista
        ]);

        $dir = now()->format('Y-m');

        $denuncia = Denuncia::create([
            'user_id' => auth()->id(),
            'team_id' => $request->team_id,
            'municipio_id' => $request->municipio_id,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'cover' => $request->file('cover') ? $request->file('cover')->store('denuncia/'.$dir,"public") : null,
        ]);

        if ($request->has('categorias')) {
            $denuncia->categories()->sync($request->categories);
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
	            $path = $file->store('denuncia/'.$dir, 'public');

	            // Crear una entrada en denunciafiles
	            DenunciaFile::create([
	                'denuncia_id' => $denuncia->id,
	                'file_path' => $path,
	                'file_type' => $type,
	            ]);
	        }
	    }

        return redirect()->route('denuncias.show', $denuncia->slug);
    }
}
