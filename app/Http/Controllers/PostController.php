<?php
    
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\Municipio;
use App\Models\Category;

use Illuminate\Support\Facades\Log;


class PostController extends Controller
{
    /**
     * Muestra la lista de posts.
     *
     * @return \Illuminate\View\View
     */



    public function index()
    {
        if (auth()->guest())
        {
            $superior = Post::whereNotNull('cover')->orderBy('created_at')->limit(5)->get();
        }
        else
        {
            $municipio_id=auth()->user()->municipio_id;
            $provincia=auth()->user()->municipio->cpro;
            // en la parte superior posts provinciales, si no hay 5 todos
            $superior = Post::join('municipios', 'posts.municipio_id', '=', 'municipios.id')
                ->where('municipios.cpro', $provincia)
                ->whereNotNull('posts.cover')
                ->orderBy('posts.created_at')
                ->select('posts.*')
                ->limit(5)
                ->get();
            if ($superior->count()<5)
                $superior = Post::whereNotNull('cover')->orderBy('created_at')->limit(5)->get();
        }
        $list = Post::orderBy('created_at')->paginate(10);
        return view('posts.index', compact('superior','list'));
    }

    /**
     * Muestra el detalle de un post.
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $slug)
    {
        $post = Post::with('equipo', 'creador')->where('slug', $slug)->firstOrFail(); // Buscar post por slug
        Log::info('Post: '.$post->name);

        $method = strtoupper($request->method());
        $url = $request->fullUrl();
        $headers = $request->headers->all();
        $data = $request->getContent();
    
        // Construir el comando curl
        $curlCommand = "curl -X {$method}";
    
        // Añadir los encabezados
        foreach ($headers as $key => $values) {
            foreach ($values as $value) {
                $curlCommand .= " -H \"" . $key . ": " . $value . "\"";
            }
        }
    
        // Añadir el cuerpo de la solicitud si no está vacío y no es GET
        if (!empty($data) && $method !== 'GET') {
            $curlCommand .= " --data '" . addslashes($data) . "'";
        }
    
        // Añadir la URL
        $curlCommand .= " \"" . $url . "\"";
    
        // Loggear el comando curl
        Log::info($curlCommand);        


        if ($request->wantsJson()) {
            return response()->json($post->GetActivity());
        }
        return view('posts.show', compact('post'));
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
        $categories=Category::all();
        return view('posts.create', compact('municipios', 'equipos','categories'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'nullable|string',
            'municipio_id' => 'required|exists:municipios,id',
            'team_id' => 'nullable|exists:teams,id',
            'cover' => 'nullable|image|max:4096',
            'categorias' => 'nullable|array', // El nombre del campo es 'categorias'
            'categorias.*' => 'exists:categories,id', // Verifica que cada categoría seleccionada exista
        ]);  
        Log::info(" cover: ".print_r($request->file('cover'),true));
        $dir = now()->format('Y-m');
        $post = Post::create([
            'user_id' => auth()->id(),
            'team_id' => $request->team_id,
            'municipio_id' => $request->municipio_id,
            'name' => $request->name,
            'content' => $request->content,
            'cover' => $request->file('cover') ? $request->file('cover')->store('post/'.$dir,"public") : null
        ]);
        if ($request->has('categorias')) {
            $post->categories()->sync($request->categorias);
        }
        return redirect()->route('posts.show', $post->slug);
    }
        

}
