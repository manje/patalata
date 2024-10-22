<?php

namespace App\Livewire\Eventos;

use Livewire\Component;
use App\Models\Evento;

class Calendar extends Component
{

    public $time;
    public $semanas=4;
    public $tabla=[];
    public $mes;
    public $hoy;
    public $mesnum;

    public function mount()    
    {
        if (auth()->guest())
            $this->municipio_id=false;
        else
            $this->municipio_id=auth()->user()->municipio_id;
        $this->time=time();
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
        $eventos = Evento::whereBetween('fecha_inicio',[date("Y-m-d 00:00:00",$inicio),date("Y-m-d 23:59:59",$fin)])->get()->sortBy('fecha_inicio'); // Obtener todos los eventos
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
        return view('livewire.eventos.calendar', ['tabla'=>$this->tabla,'mes'=>$this->mes,'hoy'=>$this->hoy,'mesnum'=>$this->mesnum]);
    
    }
}
