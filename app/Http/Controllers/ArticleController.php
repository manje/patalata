<?php
namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Place;
use App\Models\Member;
use App\Models\Team;
use App\Models\Apfile;

use App\ActivityPub\ActivityPub;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::latest()->paginate(50);
        return view('articles.index', compact('articles'));
    }

    public function show(Request $request, $slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();

        if ($request->wantsJson())
            return response()->json($article->GetActivity());

        return view('articles.show', compact('article'));
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
            return view('articles.error', [
                'error' => 'Error al obtener datos necesarios para crear una campaña'
            ]);
        }
        $article=new Article();
        $uniqid=false;
        $article->uniqid=$uniqid;
        return view('articles.create', compact('places', 'categories', 'equipos', 'article','uniqid'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'uniqid'=>'string',
            'name' => 'required|string|max:255',
            'summary' => 'required|string|max:2048',
            'content' => 'required|string|max:10000',
            'place_id' => 'nullable|exists:places,id',
            'team_id' => 'nullable|exists:teams,id',
            'categories' => 'nullable|array', // El nombre del campo es 'categorias'
            'categories.*' => 'exists:categories,id', // Verifica que cada categoría seleccionada exista
        ],
        [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no debe exceder los 250 caracteres.',
            'summary.required' => 'El resumen es obligatorio.',
            'summary.max' => 'El resumen no debe exceder los 2000 caracteres.',
            'content.required' => 'El contenido de la campaña es obligatorio.',
            'place_id.exists' => 'El lugar seleccionado no existe.',
            'team_id.exists' => 'El equipo seleccionado no existe.',
            'categories.*.exists' => 'Al menos una categoría seleccionada no existe.',
        ]
        
        );

        $dir = now()->format('Y/m');
        $destinationDir = "articles/$dir";

        // Verifica si el directorio existe, si no, lo crea
        if (!Storage::disk('public')->exists($destinationDir)) {
            Storage::disk('public')->makeDirectory($destinationDir);
        }


        $article = Article::create([
            'user_id' => Auth::user()->id,
            'team_id' => $request->team_id,
            'place_id' => $request->place_id,
            'name' => $request->name,
            'summary' => $request->summary,
            'content' => $request->content,
        ]);

        if ($request->has('categorias')) {
            $article->categories()->sync($request->categories);
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
                'apfileable_id' => $article->id,
                'apfileable_type' => 'App\Models\Article'
            ]);
        }
        return redirect()->route('articles.show', $article->slug);
    }

    public function edit(Request $request, $slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();
        $uniqid=false;
        $places = Place::all();
        $categories = Category::all();
        return view('articles.edit', compact('article', 'places', 'categories','uniqid'));
    }

    public function update(Request $request, $slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();
        Session::flash('uniqid', $request->uniqid);

        $request->validate([
            'name' => 'required|string|max:255',
            'summary' => 'required|string|max:2048',
            'content' => 'required|string|max:10000',
            'place_id' => 'nullable|exists:places,id',
            'categories' => 'nullable|array', // El nombre del campo es 'categorias'
            'categories.*' => 'exists:categories,id', // Verifica que cada categoría seleccionada exista
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

        $dir = now()->format('Y/m');
        $destinationDir = "articles/$dir";

        $article->name = $request->name;
        $article->summary = $request->summary;
        $article->content = $request->content;
        $article->place_id = $request->place_id;
        $article->save();

        if ($request->has('categorias')) {
            $article->categories()->sync($request->categories);
        }
        $old=$article->apfiles;
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
                    'apfileable_id' => $article->id,
                    'apfileable_type' => 'App\Models\Article'
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
        return redirect()->route('articles.show', $article->slug);        
    }
}
