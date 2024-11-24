<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $post->titulo }}
        </h1>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">



                    <div class="w-full h-64 relative">
                      <!-- Imagen de fondo del post -->
                      @if ($post->cover)
                      <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $post->cover) }}');"></div>
                      @endif
                      <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-center px-4">
                        <!-- Título del post -->
                        <a href="{{ route('posts.show', $post->slug) }}" class="text-white font-bold text-4xl hover:underline">
                          {{ $post->name }}
                        </a>
                        <div class="text-white text-sm">
                          <span>{{ \Carbon\Carbon::parse($post->fecha_inicio)->format('d M, H:s') }}</span> - 
                          <span> {{ $post->municipio->nombre }} ({{ $post->municipio->provincia }})</span>
                        </div>
                      </div>
                    </div>



                <div class="p-6 bg-white border-b border-gray-200 flex">



                    <div class="md:w-1/2">
                    <p class='text-4xl font-bold'>{{ $post->name }}</p>

                    @if ($post->equipo)
                        <p class='text-2xl font-bold'>{{ $post->equipo->name }}</p>
                    @else
                        <p class='text-xl '>
                          <img class="inline rounded-full w-10" src="{{ $post->creador->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                          {{ $post->creador->name }}
                        </p>
                    @endif



                    <p><strong>Fecha:</strong> {{ date('d', strtotime($post->created_at)) }} de {{ date('F', strtotime($post->created_at)) }} de {{ date('Y', strtotime($post->created_at)) }}  {{ date('H', strtotime($post->created_at)) }}:{{ date('i', strtotime($post->created_at)) }}</p>
                 
                    @if ($post->categories)
                        <p><strong>Categorías:</strong> 
                            @foreach ($post->categories as $categoria)
                                <span class="badge badge-info gap-2 mr-1">
                                    {{ $categoria->nombre }}
                                </span>
                            @endforeach
                        </p>
                    @endif
                    <p><strong>Localidad:</strong> {{ $post->municipio->nombre }} ({{ $post->municipio->provincia }})</p>
                    <p>{{ $post->content }}</p>
                    </div>
                    <div class="md:w-1/2">
                        @if($post->cover)
                            <img src="{{ asset('storage/' . $post->cover) }}" alt="{{ $post->titulo }}" class="mt-4 w-full">
                       @endif
                    </div>



                </div>
            </div>
        </div>
    </div>
</x-app-layout>
