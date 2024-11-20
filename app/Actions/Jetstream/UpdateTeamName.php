<?php

namespace App\Actions\Jetstream;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\UpdatesTeamNames;

use Illuminate\Support\Facades\Log;



class UpdateTeamName implements UpdatesTeamNames
{
    /**
     * Validate and update the given team's name.
     *
     * @param  array<string, string>  $input
     */

    /*

        Esta función está modificada para además de cambiar el nombre del equipo, también cambie la foto de perfil del equipo.

        No funciona porque no uso bien el objeto $photo que es un TemporaryUploadedFile
    */

public function update(User $user, Team $team, array $input, $photo): void
{
    // Autorizar la acción
    Gate::forUser($user)->authorize('update', $team);

    // Validar los datos de entrada
    Validator::make($input, [
        'name' => ['required', 'string', 'max:255'],
        'photo' => ['nullable', 'image', 'max:1024'], // Opcional y debe ser una imagen
    ])->validateWithBag('updateTeamName');

    // Si se proporciona una nueva foto, maneja el almacenamiento y eliminación de la anterior
    if ($photo) {
        // Elimina la foto anterior si existe
        if ($team->profile_image && \Storage::disk('public')->exists($team->profile_image)) {
            \Storage::disk('public')->delete($team->profile_image);
        }

        // Guarda la nueva foto en el almacenamiento público
        $photoPath = $photo->store('teams/profile', 'public');
    } else {
        // Mantiene la foto actual si no se proporciona una nueva
        $photoPath = $team->profile_image;
    }

    // Actualiza el equipo con el nuevo nombre y la foto de perfil
    $team->forceFill([
        'name' => $input['name'],
        'profile_image' => $photoPath,
    ])->save();

    // Log para depuración
    Log::info('Equipo actualizado: ', [
        'team_id' => $team->id,
        'name' => $input['name'],
        'profile_image' => $photoPath,
    ]);
}


    public function updateOLD(User $user, Team $team, array $input,$photo): void
    {
        Gate::forUser($user)->authorize('update', $team);

        Log::info("foto ".print_r($input,true));
        #Log::info("input foto ".print_r($input['photo'],true));

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'photo' => 'image|nullable|max:1024',
        ])->validateWithBag('updateTeamName');



        // si hay photo anterior y sube una tengo que borrar la anterior
        if ($photo) {
            
        }


        $team->forceFill([
            'name' => $input['name'],
            'profile_image' => $photo('photo') ? $photo('photo')->store('teams/profile/',"public") : null,
        ])->save();
        
        
        
    }
}
