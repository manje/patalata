<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaFile extends Model
{
    use HasFactory;
    
    protected $fillable = ['nota_id', 'file_path', 'file_type'];

    public function nota()
    {
        return $this->belongsTo(Nota::class);
    }


}
