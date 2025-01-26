<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\ModelFedi;

class Block extends Model
{
    use HasFactory;
    use ModelFedi;
    public $APtype='Block';

    protected $fillable = [
        'actor',
        'object',
    ];
}
