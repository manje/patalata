<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\ModelFedi;

class Like extends Model
{
    use HasFactory;
    
    use ModelFedi;
    public $APtype='Like';

    protected $fillable = [
        'actor',
        'object',
    ];

}
