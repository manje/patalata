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

use App\Http\Controllers\NotaController;
Route::prefix('notas')->group(function () {
    Route::get('/', [NotaController::class, 'index'])->name('notas.index');
    Route::get('/create', [NotaController::class, 'create'])->name('notas.create');
    Route::post('/', [NotaController::class, 'store'])->name('notas.store');
    Route::get('/{slug}', [NotaController::class, 'show'])->name('notas.show');
});

use App\Http\Controllers\CampaignController;
Route::get('/campaigns/@{slug}', [CampaignController::class, 'show'])->where('slug', '.*')->name('campaigns.show');
Route::resource('campaigns', CampaignController::class)->except(['show']);

use App\Http\Controllers\ArticleController;
Route::prefix('articles')->group(function () {
    Route::get('/', [ArticleController::class, 'index'])->name('articles.index'); // Mostrar lista de posts
    Route::get('/create', [ArticleController::class, 'create'])->name('articles.create'); // Formulario para crear un nuevo post
    Route::post('/', [ArticleController::class, 'store'])->name('articles.store'); // Guardar un nuevo post
    Route::get('/{slug}', [ArticleController::class, 'show'])->name('articles.show'); // Mostrar un post individual
    Route::put('/{slug}', [ArticleController::class, 'update'])->name('articles.update');
});

use App\Http\Controllers\AnnounceController;
Route::prefix('announces')->group(function () {
    Route::get('/{slug}', [AnnounceController::class, 'show'])->name('announces.show');
});

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
});

use App\Http\Controllers\ActivityPubUserController;

Route::get('/ap/users/{slug}', [ActivityPubUserController::class, 'getActor'])
    ->name('activitypub.actor')
    ->middleware('throttle:1000,1');

Route::post('/ap/users/{slug}/inbox', [ActivityPubUserController::class, 'inbox'])
    ->name('activitypub.inbox')
    ->middleware('throttle:300,1');

Route::get('/ap/users/{slug}/outbox', [ActivityPubUserController::class, 'outbox'])
    ->name('activitypub.outbox')
    ->middleware('throttle:100,1'); 
Route::get('/ap/users/{slug}/following', [ActivityPubUserController::class, 'following'])
    ->name('activitypub.following')
    ->middleware('throttle:100,1'); 
Route::get('/ap/users/{slug}/followers', [ActivityPubUserController::class, 'followers'])
    ->name('activitypub.followers')
    ->middleware('throttle:100,1'); 
Route::get('/ap/users/{slug}/members', [ActivityPubUserController::class, 'members'])
    ->name('activitypub.members')
    ->middleware('throttle:100,1'); 



Use App\Http\Controllers\FediversoController;

Route::get('/fedi', [FediversoController::class, 'index'])->name('fediverso.index'); // Formulario para crear un nuevo post
Route::get('/@{slug}', [FediversoController::class, 'profile'])->where('slug', '.*')->name('fediverso.profile');
Route::get('/capaign/@{slug}', [CampaignController::class, 'show'])->where('slug', '.*')->name('campaign.show');

Route::get('/.well-known/webfinger', [ActivityPubUserController::class, 'webFinger'])
    ->name('activitypub.webfinger');


