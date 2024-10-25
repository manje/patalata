<?php
// app/Http/Controllers/TareaController.php
namespace App\Http\Controllers;

use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TareaController extends Controller
{
    public function index()
    {
        $tareas = Tarea::with('dependencia')->withCount('usuariosQueVotaron')->orderByDesc('usuarios_que_votaron_count')->get();
        return view('tareas.index', compact('tareas'));
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
