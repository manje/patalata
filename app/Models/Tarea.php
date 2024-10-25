<?php

// app/Models/Tarea.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'dependencia_id', 'votos'];

    public function dependencia()
    {
        return $this->belongsTo(Tarea::class, 'dependencia_id');
    }

    public function dependientes()
    {
        return $this->hasMany(Tarea::class, 'dependencia_id');
    }

// app/Models/Tarea.php
public function usuariosQueVotaron()
{
    return $this->belongsToMany(User::class, 'tarea_user')->withTimestamps();
}




}
