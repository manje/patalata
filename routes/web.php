<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
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

use App\Http\Controllers\DenunciaController;

Route::resource('denuncias', DenunciaController::class);


use App\Http\Controllers\TareaController;

Route::middleware(['auth'])->group(function () {
    Route::get('/tareas', [TareaController::class, 'index'])->name('tareas.index');
    Route::post('/tareas/{tarea}/votar', [TareaController::class, 'votar'])->name('tareas.votar');
    Route::post('/tareas/{tarea}/quitar-voto', [TareaController::class, 'quitarVoto'])->name('tareas.quitarVoto');
});



