<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;
use Illuminate\Support\Str; // Importar la clase Str
use phpseclib3\Crypt\RSA;

class Team extends JetstreamTeam
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
        'profile_image'
    ];
    protected $hidden = [
        'private_key'
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($team) {
            $team->slug = static::generateUniqueSlug($team->name);
            $keyPair = RSA::createKey(2048); // Tamaño de clave recomendado: 2048 bits
            $publicKey = $keyPair->getPublicKey()->toString('PKCS8');
            $privateKey = $keyPair->toString('PKCS8');
            $team->public_key = $publicKey;
            $team->private_key = $privateKey;
        });
    }

    public static function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $count = Team::where('slug', 'LIKE', "$slug%")->count()
            + User::where('slug', 'LIKE', "$slug%")->count();
        return $count ? "{$slug}-{$count}" : $slug;
    }


}
