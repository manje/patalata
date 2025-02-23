<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Team;
use App\Models\Timeline;
use App\Models\Block;
use App\ActivityPub\ActivityPub;


use Illuminate\Support\Facades\Log;



class FediversoController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public $identidad;
    public function index(Request $request)
    {
        if ($request->has('user')) 
        {
            $u=(int)$request->get('user');
            $team=Team::find($u);
            $user=Auth::user();
            if ($team)
                $user->switchTeam($team);
            else
            $user->forceFill([
                'current_team_id' => null,
            ])->save();
            }
        $this->identidad=ActivityPub::GetIdentidad();        
        return view('fediverso.fediverso',['userfediverso'=>$this->identidad]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function profile(Request $request, string $slug)
    {
        $this->identidad=ActivityPub::GetIdentidad();        
        #Log::info(print_r($this->identidad,1));
        $name=explode("@",$slug);
        if (count($name)==1) $name[1]=$request->getHost(); /// que lo consiga internamente xxxxxxxxxxxx
        $slug=$name[0].'@'.$name[1];
        if (count($name)==2)
        {
            $actor=ActivityPub::GetActorByUsername($this->identidad,$slug);
            if (!$actor)
            {
                return response()->json('Usuario no encontrado', 404);
            }
            if ($this->identidad)
            {
                $bloqueado=Block::where('actor',$this->identidad->GetActivity()['id'])->where('object',$actor['id'])->first();
                $tehabloqueado=Block::where('actor',$actor['id'])->where('object',$this->identidad->GetActivity()['id'])->first();
            }
            else
            {
                $bloqueado=false;
                $tehabloqueado=false;
            }
            if (!empty($actor['campaign'] ?? false))
            {
                return redirect(route('campaigns.show',$slug));

            }
            return view('fediverso.profile', ['actor' => $actor,'bloqueado'=>$bloqueado,'tehabloqueado'=>$tehabloqueado,'identidad'=>$this->identidad]);
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
