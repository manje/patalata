<?php
// app/Http/Controllers/TareaController.php
namespace App\Http\Controllers;

use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;


class TareaController extends Controller
{
    public function index()
    {
        $tareas = Tarea::with('dependencia')->withCount('usuariosQueVotaron')->orderByDesc('usuarios_que_votaron_count')->get();
        // path del directorio de storage
        $texto=file_get_contents(storage_path('app/private')."/herramientas.md");
        $environment = new Environment([
            'html_input' => 'allow', // Permite HTML embebido sin escape
            'allow_unsafe_links' => false,
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());  // Núcleo de CommonMark
        $environment->addExtension(new TableExtension());           // Soporte para tablas
        $environment->addExtension(new AutolinkExtension());        // Enlaces automáticos
        $environment->addExtension(new TaskListExtension());        // Listas de tareas
        $environment->addExtension(new StrikethroughExtension());   // Texto tachado
        $converter = new CommonMarkConverter([], $environment);
        $texto = $converter->convertToHtml($texto);
        #return $texto;
        return view('tareas.index', compact('tareas','texto'));
    }

    public function votar(Tarea $tarea)
    {
        $user = Auth::user();
        if (!$tarea->usuariosQueVotaron->contains($user->id)) {
            $tarea->usuariosQueVotaron()->attach($user->id);
        }
        return redirect()->route('tareas.index');
    }

    public function quitarVoto(Tarea $tarea)
    {
        $user = Auth::user();
        if ($tarea->usuariosQueVotaron->contains($user->id)) {
            $tarea->usuariosQueVotaron()->detach($user->id);
        }
        return redirect()->route('tareas.index');
    }
}
