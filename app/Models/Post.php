<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Traits\ModelFedi;


class Post extends Model
{

    use ModelFedi;

    public $APtype='article';
    public $APtranslate=['summary'=>'name'];
    public $summary='';


    protected $fillable = [
        'user_id', 'team_id', 'municipio_id', 'name', 'content','cover',
        'slug', 'ip' 
    ];



    // Relación con el creador del post (Usuario)
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
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'post_category');
    }

    // Generar el slug automáticamente al crear el post
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $t=date("Y")."_".$post->name;
                $slug=Str::slug($t);
                if (Post::where('slug', $slug)->count()>0) 
                {
                    while (Post::where('slug', $slug)->count()>0)
                        $slug=$slug  . Str::random(3);
                }
                $post->slug = $slug;
            }
            $post->ip = request()->ip();
        });
    }



}
