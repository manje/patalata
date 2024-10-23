<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $evento->titulo }}
        </h1>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">



                    <div class="w-full h-64 relative">
                      <!-- Imagen de fondo del evento -->
                      @if ($evento->cover)
                      <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $evento->cover) }}');"></div>
                      @endif
                      <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-center px-4">
                        <!-- Título del evento -->
                        <a href="{{ route('eventos.show', $evento->slug) }}" class="text-white font-bold text-4xl hover:underline">
                          {{ $evento->titulo }}
                        </a>
                        <div class="text-white text-sm">
                          <span>{{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('d M, H:s') }}</span> - 
                          <span> {{ $evento->municipio->nombre }} ({{ $evento->municipio->provincia }})</span>
                        </div>
                      </div>
                    </div>



                <div class="p-6 bg-white border-b border-gray-200 flex">



                    <div class="md:w-1/2">
                    <p class='text-4xl font-bold'>{{ $evento->titulo }}</p>

                    @if ($evento->equipo)
                        <p class='text-2xl font-bold'>{{ $evento->equipo->name }}</p>
                    @else
                        <p class='text-xl '>
                          <img class="inline rounded-full w-10" src="{{ $evento->creador->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                          {{ $evento->creador->name }}
                        </p>
                    @endif



                    <p><strong>Fecha:</strong> {{ date('d', strtotime($evento->fecha_inicio)) }} de {{ date('F', strtotime($evento->fecha_inicio)) }} de {{ date('Y', strtotime($evento->fecha_inicio)) }}  {{ date('H', strtotime($evento->fecha_inicio)) }}:{{ date('i', strtotime($evento->fecha_inicio)) }}</p>
                 
                    @if ($evento->fecha_fin)
                        <p><strong>Fecha Fin:</strong> {{ $evento->fecha_fin }}</p>
                    @endif
                    <p><strong>{{ $evento->tipoEvento->name }}</strong></p>
                    @if ($evento->categories)
                        <p><strong>Categorías:</strong> 
                            @foreach ($evento->categories as $categoria)
                                <span class="badge badge-info gap-2 mr-1">
                                    {{ $categoria->nombre }}
                                </span>
                            @endforeach
                        </p>
                    @endif
                    <p><strong>Localidad:</strong> {{ $evento->municipio->nombre }} ({{ $evento->municipio->provincia }})</p>
                    <p>{{ $evento->descripcion }}</p>
                    </div>
                    <div class="md:w-1/2">
                        @if($evento->cover)
                            <img src="{{ asset('storage/' . $evento->cover) }}" alt="{{ $evento->titulo }}" class="mt-4 w-full">
                       @endif
                    </div>



                </div>
            </div>
        </div>
    </div>
</x-app-layout>
