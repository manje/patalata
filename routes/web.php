<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});


use App\Http\Controllers\EventoController;

Route::get('/agenda', [EventoController::class, 'index'])->name('eventos.index');
Route::get('/agenda/create', [EventoController::class, 'create'])->name('eventos.create');
Route::get('/agenda/{slug}', [EventoController::class, 'show'])->name('eventos.show');
Route::post('/agenda', [EventoController::class, 'store'])->name('eventos.store');
