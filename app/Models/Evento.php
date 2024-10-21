<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;



class Evento extends Model
{
    protected $fillable = [
        'user_id', 'team_id', 'municipio_id', 'titulo', 'descripcion',
        'fecha_inicio', 'fecha_fin', 'cover', 'slug', 'ip' , 'event_type_id'
    ];

    // Relación con el creador del evento (Usuario)
    public function creador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación con el equipo (opcional)
    public function equipo()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    // Relación con el municipio
    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }

    // Relación con la table pivote con la tabla categories
    public function categorias()
    {
        return $this->belongsToMany(Category::class, 'evento_category');
    }

    // Generar el slug automáticamente al crear el evento
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($evento) {
            if (empty($evento->slug)) {
                $t=$evento->titulo;
                $slug=Str::slug($t);
                Log::info("1er Slug: ".$slug);
                Log::info(print_r(Evento::where('slug', $slug)->count(),true));
                if (Evento::where('slug', $slug)->count()>0) 
                {
                    $slug.="_".Str::random(2);
                    Log::info("Slug con algo: ".$slug);
                    while (Evento::where('slug', $slug)->count()>0)
                        $slug=$slug  . Str::random(3);
                    Log::info("Slug final: ".$slug);
                }
                $evento->slug = $slug;
                Log::info("Slug final: ".$slug);
            }
            $evento->ip = request()->ip();
        });
    }

    public function tipoEvento()
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }



}
