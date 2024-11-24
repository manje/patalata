<x-app-layout>


    <x-slot name="header">
        <!-- titutlo y botón, a la derecha, para añadir una nueva denuncia -->
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Artículos') }}
            </h2>
            @auth
            <a href="{{ route('posts.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Crear Artículo') }}
            </a>
            @endauth
        
        </div>

    </x-slot>




    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">




@if ($superior->count() > 0)
<div class="w-full h-64 relative">
  <!-- Imagen de fondo del post -->
  <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $superior[0]->cover) }}');"></div>
  <!-- Contenido del post -->
  <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-center px-4">
    <!-- Título del post -->
    <a href="{{ route('posts.show', $superior[0]->slug) }}" class="text-white font-bold text-4xl hover:underline">
      {{ $superior[0]->name }}
    </a>
    <div class="text-white text-sm">
      <span>{{ \Carbon\Carbon::parse($superior[0]->created_at)->format('d M, H:s') }}</span> - 
      <span> {{ $superior[0]->municipio->nombre }} ({{ $superior[0]->municipio->provincia }})</span>
    </div>
  </div>
</div>
@endif



@if ($superior->count() > 4)
<div class="flex flex-wrap">
    @foreach ($superior->slice(1, 4) as $index => $post)





<div class="w-full h-64 relative md:w-1/2 lg:w-1/4 bg-gray-400 hidden
                {{ $index < 3 ? 'md:block' : '' }}
                lg:block">
  <!-- Imagen de fondo del post -->
  <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $post->cover) }}');"></div>
  <!-- Contenido del post -->
  <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-end p-4 px-4">
    <!-- Título del post -->
    <a href="{{ route('posts.show', $post->slug) }}" class="text-white font-bold text-4xl hover:underline">
      {{ $post->titulo }}
    </a>
    <div class="text-white text-sm">
      <span>{{ \Carbon\Carbon::parse($post->created_at)->format('d M, H:s') }}</span> - 
      <span> {{ $post->municipio->nombre }} ({{ $post->municipio->provincia }})</span>
    </div>
  </div>
</div>


    @endforeach
</div>
@endif













        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($list as $post)
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    {{-- Imagen de portada del post --}}
                    @if ($post->cover)
                        <img src="{{ asset('storage/' . $post->cover) }}" alt="Cover de {{ $post->name }}" class="w-full h-48 object-cover">
                      @endif

                    <div class="p-4">
                        {{-- Título del post --}}
                        <h2 class="text-lg font-bold text-gray-800 mb-2">
                          <a href="{{ route('posts.show', $post->slug) }}" class="hover:underline">
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


