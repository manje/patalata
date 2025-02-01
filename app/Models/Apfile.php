<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Apfile extends Model
{
    use HasFactory;

    protected $fillable = ['file_path', 'file_type', 'alt_text', 'apfileable_id', 'apfileable_type'];

    /**
     * Relación polimórfica.
     */
    public function apfileable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Obtiene la URL del archivo.
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
