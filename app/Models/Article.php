<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use App\Traits\ModelFedi;

class Article extends Model
{

    use ModelFedi;
    public $APtype='Article';

    protected $fillable = [
        'user_id', 'team_id', 'place_id', 'name', 'content', 'summary'
    ];

    public function creador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function equipo()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function place()
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function categories()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($article) {
            // El slug comienza con el slug del equipo, si no lo tiene, y si no del user
            if ($article->team_id)
            {
                $team=Team::find($article->team_id);
                $article->slug = $team->slug."_"; 
            }
            else
            {
                $user=User::find($article->user_id);
                $article->slug = $user->slug."_";
            }
            $article->slug .= Str::slug($article->name);
            if (strlen($article->slug)>50) $article->slug=substr($slug,0,50);
            if (self::where('slug', $article->slug)->exists()) 
                $article->slug .= '_'.Str::random(2);
            while (self::where('slug', $article->slug)->exists()) $article->slug .= Str::random(2);
            $article->ip = request()->ip();
        });
    }
}

