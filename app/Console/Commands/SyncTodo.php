<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tarea;

class SyncTodo extends Command
{
    protected $signature = 'todo:sync';
    protected $description = 'Sincroniza el archivo TODO con la base de datos de tareas';

    public function handle()
    {
        $todoPath = base_path('TODO'); // Ruta del archivo TODO

        if (!file_exists($todoPath)) {
            $this->error("El archivo TODO no existe.");
            return;
        }

        // Leer líneas del archivo TODO
        $lineas = file($todoPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $tareasEnTodo = [];

        foreach ($lineas as $linea) {
            $linea = trim($linea);
            if ($linea === '' || str_starts_with($linea, '#')) {
                continue; // Ignorar comentarios y líneas vacías
            }
            $tareasEnTodo[] = $linea;

            // Si la tarea no existe, la agregamos
            if (!Tarea::where('nombre', $linea)->exists()) {
                Tarea::create([
                    'nombre' => $linea,
                    'votos' => 0,
                ]);
                $this->info("Tarea agregada: $linea");
            }
        }

        // Eliminar tareas que ya no están en el TODO
        $tareasEliminadas = Tarea::whereNotIn('nombre', $tareasEnTodo)->delete();

        if ($tareasEliminadas > 0) {
            $this->info("Se eliminaron $tareasEliminadas tareas que ya no estaban en el TODO.");
        }

        $this->info("Sincronización completada.");
    }
}
