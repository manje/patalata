<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    protected $fillable = ['cpro', 'cmun', 'nombre'];

    // Listado de provincias
    protected static $provincias = [
        '02' => 'Albacete',
        '03' => 'Alicante/Alacant',
        '04' => 'Almería',
        '01' => 'Araba/Álava',
        '33' => 'Asturias',
        '05' => 'Ávila',
        '06' => 'Badajoz',
        '07' => 'Balears, Illes',
        '08' => 'Barcelona',
        '48' => 'Bizkaia',
        '09' => 'Burgos',
        '10' => 'Cáceres',
        '11' => 'Cádiz',
        '39' => 'Cantabria',
        '12' => 'Castellón/Castelló',
        '13' => 'Ciudad Real',
        '14' => 'Córdoba',
        '15' => 'Coruña, A',
        '16' => 'Cuenca',
        '20' => 'Gipuzkoa',
        '17' => 'Girona',
        '18' => 'Granada',
        '19' => 'Guadalajara',
        '21' => 'Huelva',
        '22' => 'Huesca',
        '23' => 'Jaén',
        '24' => 'León',
        '25' => 'Lleida',
        '27' => 'Lugo',
        '28' => 'Madrid',
        '29' => 'Málaga',
        '30' => 'Murcia',
        '31' => 'Navarra',
        '32' => 'Ourense',
        '34' => 'Palencia',
        '35' => 'Palmas, Las',
        '36' => 'Pontevedra',
        '26' => 'Rioja, La',
        '37' => 'Salamanca',
        '38' => 'Santa Cruz de Tenerife',
        '40' => 'Segovia',
        '41' => 'Sevilla',
        '42' => 'Soria',
        '43' => 'Tarragona',
        '44' => 'Teruel',
        '45' => 'Toledo',
        '46' => 'Valencia/València',
        '47' => 'Valladolid',
        '49' => 'Zamora',
        '50' => 'Zaragoza',
        '51' => 'Ceuta',
        '52' => 'Melilla',
    ];

    // Accesor para obtener el nombre de la provincia desde el código de provincia
    public function getProvinciaAttribute()
    {
        return self::$provincias[$this->cpro] ?? 'Desconocida';
    }

    // Método estático para obtener el listado de provincias
    public static function obtenerProvincias()
    {
        return self::$provincias;
    }

    public static function getProvincias()
    {
        return self::$provincias;
    }


}
