<?php

namespace App\Livewire\Teams;

use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Contracts\UpdatesTeamNames;
use Livewire\Component;
use Livewire\WithFileUploads;

 
use Illuminate\Support\Facades\Log;



class UpdateTeamNameimageForm extends Component
{
    use WithFileUploads;

    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;
    public $photo;
    public $num;

    /**
     * The component's state.
     *
     * @var array
     */
    public $state = [];

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {

        Log::info("monto team");

        $this->team = $team;
        $this->num = rand(0,100);
        $this->state = $team->withoutRelations()->toArray();
    }

    /**
     * Update the team's name.
     *
     * @param  \Laravel\Jetstream\Contracts\UpdatesTeamNames  $updater
     * @return void
     */
    public function updateTeamName(UpdatesTeamNames $updater)
    {
        $this->resetErrorBag();
        Log::info("updateTeamName");
        #Log::info("t f ".print_r($this->photo,true));
        $updater->update($this->user, $this->team, $this->state,$this->photo);
        $this->dispatch('saved');
        $this->dispatch('refresh-navigation-menu');
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return Auth::user();
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        Log::info("render");
        return view('livewire.teams.update-team-nameimage-form', [
            'team' => $this->team,
            'num' => $this->num,
            'photo' => $this->photo,
        ]);

    
    }
}
