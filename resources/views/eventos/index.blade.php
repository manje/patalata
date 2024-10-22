<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">


<div class="w-full h-64 relative">
  <!-- Imagen de fondo del evento -->
  <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $evento->cover) }}');"></div>
  <!-- Contenido del evento -->
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


<div class="flex flex-wrap">
    @foreach ($eventostodos->slice(1, 4) as $index => $evento)
        <div class="h-64 w-full md:w-1/2 lg:w-1/4 bg-gray-400 relative  hidden
                {{ $index < 3 ? 'md:block' : '' }}
                lg:block
                "
        >
            <!-- Imagen de fondo -->
            <div class="absolute inset-0 bg-cover bg-center"
                 style="background-image: url('{{ asset('storage/' . $evento->cover) }}');">
            </div>
            <div class="absolute inset-0 bg-black opacity-50"></div>
            <!-- Contenido del evento -->
            <div class="relative z-10 p-4">
                <!-- Título del evento -->
                <a href="{{ route('eventos.show', $evento->slug) }}" class="text-white font-bold text-4xl hover:underline">
                    {{ $evento->titulo }}
                </a>
                <!-- Fecha de inicio y localidad -->
                <div class="text-white text-sm mt-2">
                    <span>{{ \Carbon\Carbon::parse($evento->fecha_inicio)->format('d M, H:i') }}</span> - 
                    <span>{{ $evento->municipio->nombre }} ({{ $evento->municipio->provincia }})</span>
                </div>
            </div>
        </div>
    @endforeach
</div>



                @livewire('eventos.calendar')
            </div>
        </div>
    </div>












</x-app-layout>


