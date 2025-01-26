<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Timeline;
use App\Models\Block;
use App\ActivityPub\ActivityPub;


use Illuminate\Support\Facades\Log;



class FediversoController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public $user;
    public function index()
    {
        $this->user=Auth::user();        
        return view('fediverso.fediverso',['userfediverso'=>$this->user]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function profile(Request $request, string $slug)
    {
        $this->user=Auth::user();        
        $name=explode("@",$slug);
        if (count($name)==1)
        {
            Log::info("slug profile $slug");
            $user=User::where('slug',$slug)->first();

            if (!$user)
            {
                Log::info("404");
                return "404";
            }
            else
            {
                if ($request->wantsJson()) 
                    return response()->json($user->GetActivity());
                else
                {
                    /*
                    $bloqueado=Block::where('actor',$user->GetActivity()['id'])->where('object',$this->user->GetActivity()['id'])->first();
                    $tehabloqueado=
                    return view('fediverso.profile', ['actor' => $user->GetActivity(),'userfediverso'=>$this->user]);
                    */
                }   
                $name[0]=$slug;
                // host de la app
                $name[1]=$request->getHost();
                $slug=$name[0].'@'.$name[1];
            }
        }
        if (count($name)==2)
        {
            $actor=ActivityPub::GetActorByUsername($this->user,$slug);
            if (!$actor)
            {
                return response()->json('Usuario no encontrado', 404);
            }
            $bloqueado=Block::where('actor',$this->user->GetActivity()['id'])->where('object',$actor['id'])->first();
            $tehabloqueado=Block::where('actor',$actor['id'])->where('object',$this->user->GetActivity()['id'])->first();
            return view('fediverso.profile', ['actor' => $actor,'bloqueado'=>$bloqueado,'tehabloqueado'=>$tehabloqueado,'userfediverso'=>$this->user]);
        }
        return response()->json('Usuario no encontrado', 404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
