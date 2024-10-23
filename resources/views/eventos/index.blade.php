<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">




@if ($superior->count() > 0)
<div class="w-full h-64 relative">
  <!-- Imagen de fondo del evento -->
  <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $superior[0]->cover) }}');"></div>
  <!-- Contenido del evento -->
  <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-center px-4">
    <!-- Título del evento -->
    <a href="{{ route('eventos.show', $superior[0]->slug) }}" class="text-white font-bold text-4xl hover:underline">
      {{ $superior[0]->titulo }}
    </a>
    <div class="text-white text-sm">
      <span>{{ \Carbon\Carbon::parse($superior[0]->fecha_inicio)->format('d M, H:s') }}</span> - 
      <span> {{ $superior[0]->municipio->nombre }} ({{ $superior[0]->municipio->provincia }})</span>
    </div>
  </div>
</div>
@endif



@if ($superior->count() > 4)
<div class="flex flex-wrap">
    @foreach ($superior->slice(1, 4) as $index => $evento)





<div class="w-full h-64 relative md:w-1/2 lg:w-1/4 bg-gray-400 hidden
                {{ $index < 3 ? 'md:block' : '' }}
                lg:block">
  <!-- Imagen de fondo del evento -->
  <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $evento->cover) }}');"></div>
  <!-- Contenido del evento -->
  <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-end p-4 px-4">
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


    @endforeach
</div>
@endif


                @livewire('eventos.calendar')
            </div>
        </div>
    </div>












</x-app-layout>


