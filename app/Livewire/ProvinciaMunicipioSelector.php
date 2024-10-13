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


    public function mount($required=true,$provinciaName="provincia_id", $municipioName="municipio_id")
    {
        Log::info('provinciaName: '.$provinciaName);
        $this->provinciaName = $provinciaName;
        $this->municipioName = $municipioName;
        $this->required = $required;

        $this->provincias = Municipio::getProvincias(); // Método para obtener las provincias
    }

    public function cambio()
    {
        Log::info('provinciaId: '.$this->selectedProvincia);
        // Obtener los municipios relacionados con la provincia seleccionada
        $this->municipios = Municipio::where('cpro', $this->selectedProvincia)->get();
        $this->selectedMunicipio = null; // Resetear el municipio seleccionado
        // log
    
    }


    public function render()
    {
        Log::info('render');

        return view('livewire.provincia-municipio-selector');
    }
}
/*
<?php

namespace App\Http\Livewire;

use App\Models\Municipio;
use Livewire\Component;

class ProvinciaMunicipioSelector extends Component
{

    public function mount($provinciaName, $municipioName)
    {
        $this->provinciaName = $provinciaName;
        $this->municipioName = $municipioName;
        $this->provincias = Municipio::getProvincias(); // Método para obtener las provincias
    }

    public function updatedSelectedProvincia($provinciaId)
    {
        // Obtener los municipios relacionados con la provincia seleccionada
        $this->municipios = Municipio::where('cpro', $provinciaId)->get();
        $this->selectedMunicipio = null; // Resetear el municipio seleccionado
    }

    public function render()
    {
        return view('livewire.provincia-municipio-selector');
    }
}
*/