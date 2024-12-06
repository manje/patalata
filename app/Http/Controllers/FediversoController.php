<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Timeline;
use App\ActivityPub\ActivityPub;


use Illuminate\Support\Facades\Log;



class FediversoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        return view('fediverso.fediverso');
    
    }

    /**
     * Show the form for creating a new resource.
     */
    public function profile(Request $request, string $slug)
    {
        $name=explode("@",$slug);
        if (count($name)==1)
        {
            $user=User::where('slug',$slug)->first();
            if (!$user)
            {

            }
            return "No implementado";
        }
        if (count($name)==2)
        {
            $user=Auth::user();
            $actor=ActivityPub::GetActorByUsername($user,$slug);
            if (!$actor)
            {
                return "No encontrado";
            }
            return view('fediverso.profile', ['actor' => $actor]);
        }
        // error 404
        return "No implementado 404";
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
