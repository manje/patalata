<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Str;

class GenerateSlugs extends Command
{
    protected $signature = 'generate:slugs';
    protected $description = 'Genera slugs únicos para usuarios y equipos existentes';

    public function handle()
    {
        $this->info('Generando slugs para usuarios...');
        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                if (!$user->slug) {
                    $slug = User::generateUniqueSlug($user->name);
                    $user->slug = $slug;
                    $user->save();
                    $this->info("Slug generado para usuario {$user->name}: $slug");
                }
            }
        });

        $this->info('Generando slugs para equipos...');
        Team::chunk(100, function ($teams) {
            foreach ($teams as $team) {
                if (!$team->slug) {
                    $slug = Team::generateUniqueSlug($team->name);
                    $team->slug = $slug;
                    $team->save();
                    $this->info("Slug generado para equipo {$team->name}: $slug");
                }
            }
        });

        $this->info('Slugs generados correctamente.');
    }
}
