<x-app-layout>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="w-full aspect-[3/1] text-white relative">
                    @if (isset($campaign['image'] ))
                        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{  $campaign['image']['url'] }}');"></div>
                    @endif
                    <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-center px-4">
                        <div class='flex flex-items mt-1'>
                            <img class="rounded-full w-12 h-12 mt-2 object-cover" src="{{ $campaign['icon']['url'] ?? '' }}" alt="{{ $campaign['name'] }}" />
                            <div class="ml-2">
                                <a href="{{ route('campaigns.show', $campaign['preferredUsername']) }}" class="font-bold text-4xl hover:underline">
                                    {{ $campaign['name'] }}
                                </a>
                                <div>{{ '@' }}{{ $campaign['preferredUsername'] }}{{"@"}}{{ explode("/",$campaign['id'])[2] }}</div>
                            </div>
                        </div>
                        <div class="text-white text-sm">
                            <span>{{ \Carbon\Carbon::parse($campaign['published'])->format('d M, H:s') }}</span>
                        </div>
                        <div class='markdown line-clamp-4 truncate w-full md:w-2/5'>{!! $campaign['summary'] ?? '' !!}</div>
                    </div>

                    <div class="absolute bottom-4 right-4">
                        <livewire:fediverso.seguir :actor="$campaign" />
                    </div>                    
                </div>
                <div x-data="{seccion:'inicio'}">
                    <div class="bg-white border-b border-gray-200 flex justify-between">
                        <div @click="seccion='inicio'" class="w-full p-2 cursor-pointer text-center border-r">Inicio</div>
                        <div @click="seccion='informacion'" class="w-full p-2 cursor-pointer text-center border-r">Informacion</div>
                        <div @click="seccion='eventos'" class="w-full p-2 cursor-pointer text-center border-r">Eventos</div>
                        <div @click="seccion='inicio'" class="w-full p-2 cursor-pointer text-center border-r">Inicio</div>
                        <div @click="seccion='comentarios'" class="w-full p-2 cursor-pointer text-center">Comentarios</div>
                        @if ($rol=='admin')
                            <div @click="seccion='admin'" class="w-full p-2 cursor-pointer text-center bg-red-100">Administrar</div>
                        @endif
                    </div>
                    <div x-show="seccion=='inicio'">
                        pagina de inicio
                    </div>
                    <div x-show="seccion=='informacion'" class="p-4">
                        @if ($rol=='admin')
                        <div class="w-full">
                            <a href="{{ route('campaigns.edit', $campaign['preferredUsername']) }}" class="btn btn-primary btn-xs">Editar</a>
                        </div>
                        @endif
                        <div class="markdown">
                        {!! $campaign['summary'] ?? ''  !!}
                        </div>
                        <div class="markdown">
                        <hr>
                        {!! $campaign['content'] ?? '' !!}
                        </div>
                    </div>
                    @if ($rol=='admin')
                    <div x-show="seccion=='admin'">
                        <livewire:campaigns.admin :campaign="$campaign" />
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
