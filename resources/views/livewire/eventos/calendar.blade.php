<div class="text-gray-700">



<div wire:loading.flex class="fixed inset-0 bg-opacity-50 bg-gray-700 z-50 justify-center items-center">
  <div class="text-center">
    <p class="text-white mt-2"><span class="loading loading-ring loading-lg"></span> Cargando...</p>
  </div>
</div>


  <!-- Component Start -->
  <div class="flex flex-grow h-screen overflow-auto"> 
    <div class="flex flex-col flex-grow">
      <div class="flex items-center mt-4">
        <div class="flex ml-6">
          <button wire:click="previous">
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button wire:click="next">
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
        <h2 class="ml-2 text-xl font-bold leading-none">{{$mes}}</h2>
        <select class="ml-2 form-control select " wire:model="selectedProvincia" wire:change="cambioProvincia">
          <option value="">Todas las Provincias</option>
          @foreach ($listProvincias as $key => $value)
            <option value="{{ $key }}">{{ $value }}</option>
          @endforeach
        </select>
        <select class="ml-2 form-control select " wire:model="selectedMunicipio" wire:change="cambioMunicipio">
          <option value="">Todas las Localidades</option>
          @foreach ($listMunicipios as $value)
            <option value="{{ $value->id }}">{{ $value->nombre }}</option>
          @endforeach
        </select>
        <select class="ml-2 form-control select " wire:model="selectedTipo" wire:change="cambioTipo">
          <option value="">Todos los Tipos</option>
          @foreach ($listTipos as $value)
            <option value="{{ $value->id }}">{{ $value->name }}</option>
          @endforeach
        </select>
        
        <!-- ahora un deslplegable de categorias alineado a la derecha -->
        
        <div class="flex-grow"></div>

        @foreach ($listfiltros as $f)
        
<div class="badge badge-info gap-2 mr-1">
  <svg
    wire:click="delCategoria({{ $f->id }})"
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    class="inline-block h-4 w-4 stroke-current">
    <path
      stroke-linecap="round"
      stroke-linejoin="round"
      stroke-width="2"
      d="M6 18L18 6M6 6l12 12"></path>
  </svg>
  <!-- nombre de la categoria con id $f -->

  {{ $f->nombre }}
</div>
        @endforeach




        <select class="ml-2 form-control select " wire:model="selectedCategoria" wire:change="cambioCategoria">
          <option value="">Seleccione Categoría</option>
          @foreach ($listCategories as $value)
            <option value="{{ $value->id }}">{{ $value->nombre }}</option>
          @endforeach
        </select>

      </div>
      <div class="grid grid-cols-7 mt-4">
        <div class="pl-1 text-sm">Lunes</div>
        <div class="pl-1 text-sm">Martes</div>
        <div class="pl-1 text-sm">Miércoles</div>
        <div class="pl-1 text-sm">Jueves</div>
        <div class="pl-1 text-sm">Viernes</div>
        <div class="pl-1 text-sm">Sábado</div>
        <div class="pl-1 text-sm">Domingo</div>
      </div>
      <div class="grid flex-grow w-full h-auto grid-cols-7 gap-[1px] pt-[1px] mt-1">
        @foreach ($tabla as $week)
          @foreach ($week as $dia)
            <div class="relative flex flex-col 
              @if ($dia['mesnum'] != $mesnum)
                bg-gray-50
              @else
                bg-white
              @endif
              group">
              @if ($hoy == $dia['fecha'])
                <span><span class="mx-2 my-1 text-xs font-bold m-3 bg-blue-500 text-white px-1 rounded-full">{{ $dia['dia'] }}</span></span>
              @else
                <span class="mx-2 my-1 text-xs font-bold">{{ $dia['dia'] }}</span>
              @endif

              <div class="flex flex-col px-1 py-1 overflow-auto">
                @foreach ($dia['eventos'] as $evento)

                  <button class="flex items-center flex-shrink-0 h-5 px-1 text-xs hover:bg-gray-200">
                    <span class="flex-shrink-0 w-2 h-2 border border-gray-500 rounded-full"></span>
                    <span class="ml-2 font-light leading-none">{{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('H:i') }}</span>
                    <span class="ml-2 font-medium leading-none truncate">
                      <a href="{{ route('eventos.show', $evento->slug) }}" class="text-blue-600 hover:underline">
                        {{ $evento->titulo }}</a>
                    </span>
                  </button>
                @endforeach
              </div>
              @auth
              <a href='{{ route('eventos.create') }}?fecha={{ $dia['fecha'] }}' class="absolute bottom-0 right-0 flex items-center justify-center hidden w-6 h-6 mb-2 mr-2 text-white bg-gray-400 rounded group-hover:flex hover:bg-gray-500">
                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
                </svg>
              </a>
              @endauth
           
            </div>
          @endforeach
        @endforeach
      </div>
    </div>
  </div>
  <!-- Component End  -->

</div>

