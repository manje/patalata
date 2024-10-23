<?php

namespace App\Livewire\Eventos;

use Livewire\Component;
use App\Models\Evento;
use App\Models\Municipio;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use App\Models\EventType;


class Calendar extends Component
{

    public $time;
    public $semanas=4;
    public $tabla=[];
    public $mes;
    public $hoy;
    public $mesnum;
    public $selectedMunicipio='';
    public $selectedProvincia='';
    public $selectedCategoria='';
    public $selectedTipo='';
    
    public $listMunicipios=[];
    public $listProvincias=[];
    public $listCategories=[];
    public $listTipos=[];

    public $filtros=[];
    public $listfiltros=[];

    public function mount()    
    {
        if (auth()->guest())
            $this->municipio_id=false;
        else
            $this->municipio_id=auth()->user()->municipio_id;
        $this->time=time();
        $this->CrearTabla();
        $this->listProvincias=Municipio::getProvincias();
        $this->listMunicipios=[];
        if ($this->municipio_id)
        {
            $this->selectedMunicipio=$this->municipio_id;
            $mun=Municipio::where('id',$this->municipio_id)->firstOrFail();
            $this->listMunicipios=Municipio::where("cpro",$mun->cpro)->get();
            $this->selectedProvincia=$mun->cpro;
        }
        $this->listCategories=Category::all();
        $this->listTipos=EventType::all();

    }

    public function cambioProvincia()
    {
        $this->selectedMunicipio='';
        $this->listMunicipios=Municipio::where("cpro",$this->selectedProvincia)->get();
        $this->CrearTabla();
    }

    public function cambioMunicipio()
    {
        $this->CrearTabla();
    }

    public function cambioTipo()
    {
        $this->CrearTabla();
    }

    public function cambioCategoria()
    {
        $this->filtros[]=$this->selectedCategoria;
        // calculo de nuevo el listado de categorias (sin meter las que están en filtros) y reestablezco el valor del desplegable
        $this->listCategories=Category::whereNotIn('id',$this->filtros)->get();
        $this->selectedCategoria='';
        $this->listfiltros=Category::whereIn('id',$this->filtros)->get();
        $this->CrearTabla();
    }

    public function delCategoria($id)
    {
        $this->filtros=array_diff($this->filtros,[$id]);
        $this->listCategories=Category::whereNotIn('id',$this->filtros)->get();
        $this->listfiltros=Category::whereIn('id',$this->filtros)->get();
        $this->CrearTabla();
    }




    public function next()
    {
        $this->time=$this->time+60*60*24*7;
        $this->CrearTabla();
    }

    public function previous()
    {
        $this->time=$this->time-60*60*24*7;
        $this->CrearTabla();
    }

    public function CrearTabla()
    {
        $semana=date('W',$this->time);
        // time del lunes
        $inicio=$this->time;
        while (date("w",$inicio)!=1)
            $inicio-=60*60*24;
        $domingo=$inicio;
        while (date("w",$domingo)!=0)
            $domingo+=60*60*24;
        $this->mesnum=date("m",$inicio);
        $fin=$inicio+60*60*24*7*$this->semanas-60*60*24;
        $tabla=[];
        $i=$inicio;
        while ($i <= $fin)
        {
            $w=date("W",$i);
            $d=date("d",$i);
            if (!isset($tabla[$w]))
                $tabla[$w]=[];
            $tabla[$w][$d]=["dia"=>date("d",$i),"eventos"=>[],"fecha"=>date("Y-m-d",$i),"mesnum"=>date("m",$i)];
            $i+=60*60*24;
        }
        // desde $desde hasta $hasta ambos incluidos
        $eventos = Evento::whereBetween('fecha_inicio',[date("Y-m-d 00:00:00",$inicio),date("Y-m-d 23:59:59",$fin)]);
        if ($this->selectedMunicipio)
            $eventos=$eventos->where('municipio_id',$this->selectedMunicipio);
        else
            if ($this->selectedProvincia)
            {
                $municipios=Municipio::where('cpro',$this->selectedProvincia)->get();
                $eventos=$eventos->whereIn('municipio_id',$municipios->pluck('id'));
            }
        if ($this->selectedTipo)
            $eventos=$eventos->where('event_type_id',$this->selectedTipo);
       
        $eventos=$eventos->get()->sortBy('fecha_inicio'); // Obtener todos los eventos

if (!empty($this->filtros)) {
    // Con estar en uno de los filtros tiene que mostrarse
    $eventos = $eventos->filter(function ($evento) {
        return $evento->categories->pluck('id')->intersect($this->filtros)->isNotEmpty();
    });
}

        foreach ($eventos as $evento)
        {
            $numw=date("W",strtotime($evento->fecha_inicio));
            $dia=date("d",strtotime($evento->fecha_inicio));
            if (isset($tabla[$numw][$dia]))
                $tabla[$numw][$dia]["eventos"][]=$evento;
        }
        $this->tabla=$tabla;
        $this->mes=date("F Y",$inicio+60*60*24*7);
        $this->hoy=date("Y-m-d");
    }

    public function render()
    {
        Log::info($this->listMunicipios);
        return view('livewire.eventos.calendar', ['tabla'=>$this->tabla,'mes'=>$this->mes,'hoy'=>$this->hoy,
            'mesnum'=>$this->mesnum,
            'listProvincias'=>$this->listProvincias,
            'listMunicipios'=>$this->listMunicipios,
            'listCategories'=>$this->listCategories,
            'selectedProvincia'=>$this->selectedProvincia,
            'selectedMunicipio'=>$this->selectedMunicipio,
            'selectedCategoria'=>$this->selectedCategoria,
            'filtros'=>$this->filtros,
            'listfiltros'=>$this->listfiltros,
            'listTipos'=>$this->listTipos,
            'selectedTipo'=>$this->selectedTipo





    ]);
    
    }
}
