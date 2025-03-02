<x-app-layout>


    <x-slot name="header">
        <!-- titutlo y botón, a la derecha, para añadir una nueva denuncia -->
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Eventos') }}
            </h2>
            @auth
            <a href="{{ route('events.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Crear Evento') }}
            </a>
            @endauth
        
        </div>

    </x-slot>




    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">




@if (count($events) > 0)
<div class="w-full h-64 relative">
  <!-- Imagen de fondo del post -->
  @foreach ($events[0]->apfiles as $file)
      @if (explode('/',$file->file_type)[0]=='image')
          <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{  $file->getUrlAttribute() }}');"></div>
          @break
      @endif
  @endforeach
  <!-- Contenido del post -->
  <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-center px-4">
    <!-- Título del post -->
    <a href="{{ route('events.show', $events[0]->slug) }}" class="text-white font-bold text-4xl hover:underline">
      {{ $events[0]->name }}
    </a>
    <div class="text-white text-sm">
      <span>{{ $events[0]->created_at->diffForHumans() }}</span>
      @if ($events[0]->place)
         - <span> {{ $events[0]->place->name }} </span>
      @endif
    </div>
  </div>
</div>
@endif



@if ($events->count() > 4)
<div class="flex flex-wrap">
    @foreach ($events->slice(1, 4) as $index => $post)
      <div class="w-full h-64 relative md:w-1/2 lg:w-1/4 bg-gray-400 hidden
                      {{ $index < 3 ? 'md:block' : '' }}
                      lg:block">
        <!-- Imagen de fondo del post -->
        @foreach ($post->apfiles as $file)
          @if (explode('/',$file->file_type)[0]=='image')
              <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{  $file->getUrlAttribute() }}');"></div>
              @break
          @endif
        @endforeach
        <!-- Contenido del post -->
        <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-end p-4 px-4">
          <!-- Título del post -->
          <a href="{{ route('events.show', $post->slug) }}" class="text-white font-bold text-4xl hover:underline">
            {{ $post->name }}
          </a>
          <div class="text-white text-sm">
            <span>{{ $post->created_at->diffForHumans() }}</span> 
            @if ($post->place)
              - <span> {{ $post->place->name }} </span>
            @endif
          </div>
        </div>
      </div>


    @endforeach
</div>
@endif


        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
            @foreach ($events as $k=> $post)
                @if ($k==0) 
                  @continue
                @endif
                @if ($events->count() > 4)
                    @if ($k < 5)
                      @continue
                    @endif
                @endif
                
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    {{-- Imagen de portada del post --}}

                    @foreach ($post->apfiles as $file)
                      @if (explode('/',$file->file_type)[0]=='image')
                          <img src="{{ $file->getUrlAttribute()  }}" alt="Cover de {{ $file->alt_text }}" class="w-full h-48 object-cover">
                          @break
                      @endif
                    @endforeach

                    <div class="p-4">
                        {{-- Título del post --}}
                        <h2 class="text-lg font-bold text-gray-800 mb-2">
                          <a href="{{ route('events.show', $post->slug) }}" class="hover:underline">
                            {{ $post->name }}
                          </a>
                        </h2>

                        {{-- Extracto del texto --}}
                        <p class="text-gray-600 text-sm mb-4">
                            {{ Str::limit($post->text, 100, '...') }}
                        </p>

                        {{-- Información del autor --}}
                        <div class="flex items-center">
                            <img 
                                src="{{ $post->creador->profile_photo_url ?? asset('images/default-profile.png') }}" 
                                alt="Foto de {{ $post->creador->profile_photo_url }}" 
                                class="w-10 h-10 rounded-full mr-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $post->creador->name }}</p>
                                <p class="text-xs text-gray-500">Publicado el {{ $post->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>























            </div>
        </div>
    </div>












</x-app-layout>


