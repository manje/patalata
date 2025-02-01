<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'geonames_id',
        'latitude',
        'longitude',
        'radius'
    ];

    /**
     * Obtener la ubicación en formato JSON.
     */
    public function toGeoJson(): array
    {
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [$this->longitude, $this->latitude],
            ],
            'properties' => [
                'name' => $this->name,
                'geonames_id' => $this->geonames_id,
                'radius_km' => $this->radius,
            ],
        ];
    }
}
