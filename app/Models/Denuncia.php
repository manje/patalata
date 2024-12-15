<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Denuncia extends Model
{
    protected $fillable = [
        'user_id', 'team_id', 'municipio_id', 'titulo', 'descripcion',
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

	public function denunciaFiles()
	{
	    return $this->hasMany(DenunciaFile::class);
	}

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($denuncia) {
            if (empty($denuncia->slug)) {
                $slug = Str::slug(date("Y")."_".$denuncia->titulo);
                if (strlen($slug)>30) $slug=substr($slug,0,30);
                while (self::where('slug', $slug)->exists()) {
                    $slug .= "_" . Str::random(2);
                }
                $denuncia->slug = $slug;
            }
            $denuncia->ip = request()->ip();
        });
    }
}

