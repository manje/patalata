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

use App\Http\Controllers\PostController;
Route::resource('/articulo', PostController::class);


Route::prefix('articulos')->group(function () {
    Route::get('/', [PostController::class, 'index'])->name('posts.index'); // Mostrar lista de posts
    Route::get('/create', [PostController::class, 'create'])->name('posts.create'); // Formulario para crear un nuevo post
    Route::post('/', [PostController::class, 'store'])->name('posts.store'); // Guardar un nuevo post
    Route::get('/{slug}', [PostController::class, 'show'])->name('posts.show'); // Mostrar un post individual
    #Route::get('/{post}/edit', [PostController::class, 'edit'])->name('posts.edit'); // Formulario para editar un post
    #Route::put('/{post}', [PostController::class, 'update'])->name('posts.update'); // Actualizar un post existente
    #Route::delete('/{post}', [PostController::class, 'destroy'])->name('posts.destroy'); // Eliminar un post
});

use App\Http\Controllers\ActivityPubUserController;

Route::get('/ap/users/{slug}', [ActivityPubUserController::class, 'getActor'])
    ->name('activitypub.actor')
    ->middleware('throttle:10,1'); // Opcional: limitar peticiones por seguridad

Route::post('/ap/users/{slug}/inbox', [ActivityPubUserController::class, 'inbox'])
    ->name('activitypub.inbox')
    ->middleware('throttle:10,1'); // Opcional: limitar peticiones por seguridad

Route::get('/ap/users/{slug}/outbox', [ActivityPubUserController::class, 'outbox'])
    ->name('activitypub.outbox')
    ->middleware('throttle:10,1'); // Opcional: limitar peticiones por seguridad


Route::get('/.well-known/webfinger', [ActivityPubUserController::class, 'webFinger'])
    ->name('activitypub.webfinger');
