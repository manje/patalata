<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DenunciaFile extends Model
{
    use HasFactory;
    
    protected $fillable = ['denuncia_id', 'file_path', 'file_type'];

    public function denuncia()
    {
        return $this->belongsTo(Denuncia::class);
    }


}
