<x-app-layout>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="w-full aspect-[3/1] text-white relative">
                    @if (isset($article['image'] ))
                        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{  $article['image']['url'] }}');"></div>
                    @endif
                    <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-center px-4">
                        <div class='flex flex-items mt-1'>
                            <img class="rounded-full w-12 h-12 mt-2 object-cover" src="{{ $article['icon']['url'] ?? '' }}" alt="{{ $article['name'] }}" />
                            <div class="ml-2">
                                <a href="{{ route('articles.show', $article['slug']) }}" class="font-bold text-4xl hover:underline">
                                    {{ $article['name'] }}
                                </a>
                                <div>{{ '@' }}{{ $article['slug'] }}</div>
                            </div>
                        </div>
                        <div class="text-white text-sm">
                            <span>{{ \Carbon\Carbon::parse($article['created_at'])->format('d M, H:s') }}</span>
                        </div>
                        <div class='markdown line-clamp-4 truncate w-full md:w-3/5'>{!! $article['summary'] ?? '' !!}</div>
                    </div>

                    <div class="absolute bottom-4 right-4">
                        <livewire:fediverso.seguir :actor="$article" />
                    </div>                    
                </div>
                <div x-data="{seccion:'informacion'}">
                    <div class="bg-white border-b border-gray-200 flex justify-between">
                        <div @click="seccion='inicio'" class="w-full p-2 cursor-pointer text-center border-r">Inicio</div>
                        <div @click="seccion='informacion'" class="w-full p-2 cursor-pointer text-center border-r">Informacion</div>
                        <div @click="seccion='members'" class="w-full p-2 cursor-pointer text-center border-r">Miembros</div>
                        <div @click="seccion='eventos'" class="w-full p-2 cursor-pointer text-center border-r">Eventos</div>
                        <div @click="seccion='inicio'" class="w-full p-2 cursor-pointer text-center border-r">Inicio</div>
                        <div @click="seccion='comentarios'" class="w-full p-2 cursor-pointer text-center">Comentarios</div>
                    </div>
                    <div x-show="seccion=='informacion'" class="p-4">
                        <div class="markdown">
                        {!! $article['summary'] ?? ''  !!}
                        </div>
                        <div class="markdown">
                        <hr>
                        {!! $article['content'] ?? '' !!}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
