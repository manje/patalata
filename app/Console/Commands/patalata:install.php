<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class patalata:install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:patalata:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Define las rutas de los directorios que deseas crear
        $directories = [
            storage_path('framework/views'),
            storage_path('framework/cache'),
            storage_path('logs'),
            bootstrap_path('cache'),
        ];

        // Crea los directorios si no existen
        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
                $this->info("Directorio creado: $directory");
            } else {
                $this->info("El directorio ya existe: $directory");
            }
        }

        $this->info('Todos los directorios necesarios han sido verificados y creados.');
    }


}



<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupDirectories extends Command
{
    protected $signature = 'setup:directories';
    protected $description = 'Create necessary directories for the application';

    public function handle()
    {
        // Define las rutas de los directorios que deseas crear
        $directories = [
            storage_path('framework/views'),
            storage_path('framework/cache'),
            storage_path('logs'),
            bootstrap_path('cache'),
        ];

        // Crea los directorios si no existen
        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
                $this->info("Directorio creado: $directory");
            } else {
                $this->info("El directorio ya existe: $directory");
            }
        }

        $this->info('Todos los directorios necesarios han sido verificados y creados.');
    }
}
