<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Str;
use phpseclib3\Crypt\RSA;

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
                if (!$user->private_key) {
                    $keyPair = RSA::createKey(2048); // Tamaño de clave recomendado: 2048 bits
                    $publicKey = $keyPair->getPublicKey()->toString('PKCS8');
                    $privateKey = $keyPair->toString('PKCS8');
                    $user->public_key = $publicKey;
                    $user->private_key = $privateKey;
                    $user->save();
                    $this->info("Claves RSA generadas para usuario {$user->name}");
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
                if (!$team->private_key) {
                    $keyPair = RSA::createKey(2048); // Tamaño de clave recomendado: 2048 bits
                    $publicKey = $keyPair->getPublicKey()->toString('PKCS8');
                    $privateKey = $keyPair->toString('PKCS8');
                    $team->public_key = $publicKey;
                    $team->private_key = $privateKey;
                    $team->save();
                    $this->info("Claves RSA generadas para equipo {$team->name}");
                }
            }
        });

        $this->info('Slugs generados correctamente.');
    }
}
