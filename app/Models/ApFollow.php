<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApFollow extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'actor_id',
        'actor_type',
        'actor_preferred_username',
        'actor_inbox',
        'user_id',
        'followed_at',
    ];

    /**
     * Define la relación con el usuario local seguido.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
