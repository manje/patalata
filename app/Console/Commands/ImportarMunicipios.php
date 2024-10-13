<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Municipio;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportarMunicipios extends Command
{
    protected $signature = 'municipios:importar';
    protected $description = 'Descarga y procesa el listado de municipios desde el INE';

    public function handle()
    {
        // URL del archivo Excel
        $url = 'https://www.ine.es/daco/daco42/codmun/diccionario24.xlsx';

        // Descargar el archivo Excel
        $this->info('Descargando archivo...');
        $response = Http::get($url);
        $filePath = storage_path('app/diccionario24.xlsx');
        file_put_contents($filePath, $response->body());

        $this->info('Procesando archivo Excel...');

        // Leer el archivo Excel
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        // Iterar desde la fila 3 hasta la última fila
        $highestRow = $worksheet->getHighestRow(); 
        for ($row = 3; $row <= $highestRow; $row++) {
            $cpro = $worksheet->getCell("B$row")->getValue();
            $cmun = $worksheet->getCell("C$row")->getValue();
            $nombre = $worksheet->getCell("E$row")->getValue();

            // Insertar o actualizar el municipio en la base de datos
            Municipio::updateOrCreate(
                ['cpro' => $cpro, 'cmun' => $cmun],
                ['nombre' => $nombre]
            );
        }

        $this->info('Municipios importados exitosamente.');
    }
}
