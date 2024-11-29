<x-app-layout>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="w-full h-64 relative">
                    @if ($nota->cover)
                    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $nota->cover) }}');"></div>
                    @endif
                    <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-center px-4">
                    <a href="{{ route('notas.show', $nota->slug) }}" class="text-white font-bold text-4xl hover:underline">
                        {{ $nota->content }}
                    </a>
                    <div class="text-white text-sm">
                        <span>{{ \Carbon\Carbon::parse($nota->fecha_inicio)->format('d M, H:s') }}</span> - 
                        <span> {{ $nota->municipio->nombre }} ({{ $nota->municipio->provincia }})</span>
                    </div>
                    </div>
                </div>

                <div class="p-6 bg-white border-b border-gray-200">


                    <p class='text-4xl font-bold'>{{ $nota->content }}</p>

                    @if ($nota->equipo)
                        <p class='text-2xl font-bold'>{{ $nota->equipo->name }}</p>
                    @else
                        <p class='text-xl '>
                          <img class="inline rounded-full w-10" src="{{ $nota->creador->profile_photo_url }}" alt="{{ $nota->creador->name }}" />
                          {{ $nota->creador->name }}
                        </p>
                    @endif


                    <p> {{ $nota->content }}</p>







<!-- Mostrar archivos relacionados -->
<div class="flex flex-wrap -mx-2">
    @if($nota->cover)
        <div class="w-full md:w-1/2 lg:w-1/4 p-2">
            <img src="{{ asset('storage/' . $nota->cover) }}" alt="Imagen adjunta" class="img-fluid w-full h-auto">
            <!--br>
            <a href="{{ asset('storage/' . $nota->cover) }}" target="_blank" class="btn btn-wide">
                <i class="fas fa-download"></i> 
            Descargar archivo</a-->
        

        </div>
    @endif
    @foreach ($nota->notaFiles as $file)
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
        </div>
    @endforeach
</div>










                </div>
            </div>
        </div>
    </div>
</x-app-layout>
