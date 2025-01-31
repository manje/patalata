<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        $places = Place::all();  // Obtener todas las localidades
        return view('auth.register', compact('places'));  // Pasar a la vista
    }
}
