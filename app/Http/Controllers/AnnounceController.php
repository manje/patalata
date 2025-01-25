<?php
namespace App\Http\Controllers;

use App\Models\Announce;
use App\Models\Municipio;
use App\Models\Category;
use App\Models\AnnounceFile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnnounceController extends Controller
{
    public function show(Request $request, $slug)
    {   
        $announce = Announce::where('id', (int)$slug)->firstOrFail();
        return response()->json($announce->GetActivity());
    }
}
