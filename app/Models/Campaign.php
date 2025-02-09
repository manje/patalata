<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use phpseclib3\Crypt\RSA;
use App\Traits\ModelFedi;
use App\Model\Memeber;
use Storage;

class Campaign extends Model
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory;
    use ModelFedi;

    public $APtype='Group';

    protected $appends = [
        'profile_photo_url','image_url'
    ];


    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_image)
            return Storage::disk('public')->url($this->profile_image);
        return null;
    }

    public function getImageUrlAttribute()
    {
        if ($this->image)
            return Storage::disk('public')->url($this->image);
        return null;
    }


    protected $fillable = [
        'name',
        'profile_image',
        'image',
        'slug',
        'place_id',
        'summary',
        'content',
        'team_id'];
    protected $hidden = [
        'private_key'
    ];

    public function Rol($user)
    {
        $rol=Member::where('object',$user->GetActivity()['id'])->where('object',$this->GetActivity()['id'])->first();
        if ($rol)
            return $rol->status;
        if (!$rol)
        {
            $equipos = $user->allTeams();
            foreach ($equipos as $t)
            {
                $getrol=Member::where('object',$t->GetActivity()['id'])->where('actor',$this->GetActivity()['id'])->first();
                if ($getrol)
                {
                    if ($rol!='admin')
                        $rol=$getrol->status;
                    if ($getrol->status=='admin')
                        $rol=$getrol->status;

                }
            }
        }
        return $rol;
    }

    // Relación con la table pivote con la tabla categories
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'campaign_category', 'campaign_id', 'category_id');
    }


    protected static function boot()
    {
        parent::boot();
        static::creating(function ($team) {
            $keyPair = RSA::createKey(2048); // Tamaño de clave recomendado: 2048 bits
            $publicKey = $keyPair->getPublicKey()->toString('PKCS8');
            $privateKey = $keyPair->toString('PKCS8');
            $team->public_key = $publicKey;
            $team->private_key = $privateKey;
        });
    }

 

}
