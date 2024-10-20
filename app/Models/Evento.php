<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Evento extends Model
{
    protected $fillable = [
        'user_id', 'team_id', 'municipio_id', 'titulo', 'descripcion',
        'fecha_inicio', 'fecha_fin', 'cover', 'slug'
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
                $evento->slug = Str::slug($evento->titulo, '-') . '-' . Str::random(6);
            }
        });
    }




}
