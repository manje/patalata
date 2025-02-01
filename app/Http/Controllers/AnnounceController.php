<?php
namespace App\Http\Controllers;

use App\Models\Announce;
use App\Models\Municipio;
use App\Models\Category;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnnounceController extends Controller
{
    public function show(Request $request, $slug)
    {   
        $announce = Announce::find( (int)$slug);
        if (!$announce)
            return response()->json(['error' => 'No se ha encontrado la actividad'], 404);
        return response()->json($announce->GetActivity());
    }
}
