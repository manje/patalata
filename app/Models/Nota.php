<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use App\Traits\ModelFedi;



class Nota extends Model
{

    use ModelFedi;
    public $APtype='Note';

    protected $fillable = [
        'user_id', 'team_id', 'municipio_id', 'content','sensitive','summary',
        'cover', 'slug'
    ];

    public function creador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function equipo()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }

    public function categories()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

	public function NotaFiles()
	{
	    return $this->hasMany(NotaFile::class);
	}

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($nota) {
            $nota->ip = request()->ip();
        });
        static::created(function ($nota) {
            $nota->slug = (string) $nota->id;
            $nota->saveQuietly();
        });
    }
}

