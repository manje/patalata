<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\ModelFedi;

class Announce extends Model
{
    use HasFactory;
    use ModelFedi;
    public $APtype='Announce';

    protected $fillable = [
        'actor',
        'object',
    ];

}
