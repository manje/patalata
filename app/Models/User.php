<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Support\Str; // Importar la clase Str
use Illuminate\Contracts\Auth\MustVerifyEmail; 

use phpseclib3\Crypt\RSA;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'municipio_id',
        'slug',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'private_key'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            $user->slug = static::generateUniqueSlug($user->name);
            $keyPair = RSA::createKey(2048); // Tamaño de clave recomendado: 2048 bits
            $publicKey = $keyPair->getPublicKey()->toString('PKCS8');
            $privateKey = $keyPair->toString('PKCS8');
            $user->public_key = $publicKey;
            $user->private_key = $privateKey;
        });
    }

    public static function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $count = User::where('slug', 'LIKE', "$slug%")->count()
            + Team::where('slug', 'LIKE', "$slug%")->count();
        return $count ? "{$slug}-{$count}" : $slug;
    }

    public function getHandleAttribute()
    {
        return "@{$this->slug}@".config('app.url');
    }

}



