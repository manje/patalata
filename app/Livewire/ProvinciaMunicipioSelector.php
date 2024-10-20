<?php

namespace App\Livewire;

use App\Models\Municipio;
use Livewire\Component;
use Log;

class ProvinciaMunicipioSelector extends Component
{
    public $provincias;
    public $municipios = [];
    public $selectedProvincia = null;
    public $selectedMunicipio = null;
    public $required = true;


    public $provinciaName;  // Nombre del select de provincias
    public $municipioName;  // Nombre del select de municipios


    public function mount($required=true,$provinciaName="provincia_id", $municipioName="municipio_id", $selectedProvincia=null, $selectedMunicipio=null)
    {
        $this->provinciaName = $provinciaName;
        $this->municipioName = $municipioName;
        $this->selectedProvincia = $selectedProvincia;
        $this->selectedMunicipio = $selectedMunicipio;
        $this->required = $required;

        $this->provincias = Municipio::getProvincias(); // Método para obtener las provincias
        // Obtener los municipios relacionados con la provincia seleccionada
        $this->municipios = Municipio::where('cpro', $this->selectedProvincia)->get();
    
    }

    public function cambio()
    {
        // Obtener los municipios relacionados con la provincia seleccionada
        $this->municipios = Municipio::where('cpro', $this->selectedProvincia)->get();
        $this->selectedMunicipio = null; // Resetear el municipio seleccionado
    }

    public function cambiomun()
    {
        $this->dispatch('municipioSelected',$this->selectedMunicipio);
    
    }

    public function render()
    {
        return view('livewire.provincia-municipio-selector');
    }
}
