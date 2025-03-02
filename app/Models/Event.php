<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use App\Traits\ModelFedi;

use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class Event extends Model
{

    use ModelFedi;
    use HasSpatial;
    public $APtype='Event';

    protected $fillable = [
        'user_id', 'team_id', 'place_id', 'name', 'content', 'summary','startTime','endTime','coordinates',
        'location_name','location_addressCountry','location_addressLocality','location_addressRegion','location_postalCode','location_streetAddress',
    ];

    protected $casts = [
        'categories' => 'array',
        'startTime' => 'datetime',
        'endTime' => 'datetime',
        'coordinates' => Point::class
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
        static::creating(function ($event) {
            // El slug comienza con el slug del equipo, si no lo tiene, y si no del user
            if ($event->team_id)
            {
                $team=Team::find($event->team_id);
                $event->slug = $team->slug."_"; 
            }
            else
            {
                $user=User::find($event->user_id);
                $event->slug = $user->slug."_";
            }
            $event->slug.='event_';
            $event->slug .= Str::slug($event->name);
            if (strlen($event->slug)>60) $event->slug=substr($slug,0,60);
            if (self::where('slug', $event->slug)->exists()) 
                $event->slug .= '_'.Str::random(2);
            while (self::where('slug', $event->slug)->exists()) $event->slug .= Str::random(2);
            $event->ip = request()->ip();
        });
    }
}

