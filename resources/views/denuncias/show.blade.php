<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $denuncia->titulo }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="w-full h-64 relative">
                    <!-- Imagen de fondo del evento -->
                    @if ($denuncia->cover)
                    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $denuncia->cover) }}');"></div>
                    @endif
                    <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-center px-4">
                    <!-- Título del evento -->
                    <a href="{{ route('eventos.show', $denuncia->slug) }}" class="text-white font-bold text-4xl hover:underline">
                        {{ $denuncia->titulo }}
                    </a>
                    <div class="text-white text-sm">
                        <span>{{ \Carbon\Carbon::parse($denuncia->fecha_inicio)->format('d M, H:s') }}</span> - 
                        <span> {{ $denuncia->municipio->nombre }} ({{ $denuncia->municipio->provincia }})</span>
                    </div>
                    </div>
                </div>

                <div class="p-6 bg-white border-b border-gray-200">


                    <p class='text-4xl font-bold'>{{ $denuncia->titulo }}</p>

                    @if ($denuncia->equipo)
                        <p class='text-2xl font-bold'>{{ $denuncia->equipo->name }}</p>
                    @else
                        <p class='text-xl '>
                          <img class="inline rounded-full w-10" src="{{ $denuncia->creador->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                          {{ $denuncia->creador->name }}
                        </p>
                    @endif


                    <p> {{ $denuncia->descripcion }}</p>







<!-- Mostrar archivos relacionados -->
<div class="flex flex-wrap -mx-2">
    @if($denuncia->cover)
        <div class="w-full md:w-1/2 lg:w-1/4 p-2">
            <img src="{{ asset('storage/' . $denuncia->cover) }}" alt="Imagen adjunta" class="img-fluid w-full h-auto">
            <!--br>
            <a href="{{ asset('storage/' . $denuncia->cover) }}" target="_blank" class="btn btn-wide">
                <i class="fas fa-download"></i> 
            Descargar archivo</a-->
        

        </div>
    @endif
    @foreach ($denuncia->denunciaFiles as $file)
        @php
            $fileType = pathinfo($file->file_path, PATHINFO_EXTENSION);
        @endphp
        <div class="w-full md:w-1/2 lg:w-1/4 p-2">
            @if(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif','webp']))
                <!-- Mostrar imagen -->
                <img src="{{ Storage::url($file->file_path) }}" alt="Imagen adjunta" class="img-fluid w-full h-auto">
            @elseif($fileType === 'mp4')
                <!-- Mostrar video -->
                <video controls class="w-full h-auto">
                    <source src="{{ Storage::url($file->file_path) }}" type="video/mp4">
                    Tu navegador no soporta la reproducción de videos.
                </video>
            @elseif($fileType === 'mp3')
                <!-- Mostrar audio -->
                <audio controls class="w-full h-auto">
                    <source src="{{ Storage::url($file->file_path) }}" type="audio/mpeg">
                    Tu navegador no soporta la reproducción de audio.
                </audio>
            @else
                <!-- Mostrar enlace de archivo para otros tipos -->
                <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="btn btn-xs btn-block">
                    <i class="fas fa-download"></i> 
                Descargar archivo</a>

            @endif
                <!--br>
                <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="btn btn-xs btn-block">
                    <i class="fas fa-download"></i> 
                Descargar archivo</a-->
        </div>
    @endforeach
</div>










                </div>
            </div>
        </div>
    </div>
</x-app-layout>
