<x-app-layout>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="w-full aspect-[3/1] text-white relative">
                    @foreach ($event->apfiles as $file)
                        @if (explode('/',$file->file_type)[0] == 'image')
                            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $file->getUrlAttribute() }}');"></div>
                            @break
                        @endif
                    @endforeach
                    <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-center px-4">
                        <div class="flex items-center mt-1">
                            <img class="rounded-full w-12 h-12 object-cover" src="{{ data_get($event->GetActor(true), 'icon.url', '') }}" alt="{{ $event->GetActor(true)['name'] }}" />
                            <div class="ml-2">
                                <a href="{{ route('events.show', $event['slug']) }}" class="font-bold text-4xl hover:underline">
                                    {{ $event['name'] }}
                                </a>
                                <div>{{ '@' }}{{ $event->GetActor(true)['preferredUsername'] }}</div>
                            </div>
                        </div>
                        <div class="text-white text-sm">
                            <span>{{ \Carbon\Carbon::parse($event['startTime'])->format('d M, H:i') }} - 
                            @if($event['endTime'])
                                {{ \Carbon\Carbon::parse($event['endTime'])->format('d M, H:i') }}
                                <div class="mt-2 text-sm text-gray-300">
                                    Duración: {{ \Carbon\Carbon::parse($event['startTime'])->diffInMinutes($event['endTime']) }} minutos
                                </div>
                            @else
                                {{ __('Sin fecha de finalización') }}
                            @endif
                            </span>
                        </div>
                        <div class="markdown line-clamp-4 truncate w-full md:w-3/5">{!! $event['summary'] ?? '' !!}</div>
                    </div>

                    <div class="absolute bottom-4 right-4 flex items-center">
                        @if ($event->editable())
                            <a href="{{ route('events.edit', $event['slug']) }}" class="btn btn-secondary btn-sm mr-2">Editar</a>
                        @endif
                        <livewire:fediverso.seguir :actor="$event->GetActor()" />
                    </div>                    
                </div>
                <div class="p-2">

                    <div class="mt-4 w-full flex flex-items">
                        <div class="text-sm text-gray-600">
                            <h3 class="text-lg font-bold text-gray-900">Localización</h3>
                            @if($event->location_name)
                                <p><strong>Nombre del lugar:</strong> {{ $event->location_name }}</p>
                            @endif
                            @if($event->location_addressStreetAddress)
                                <p><strong>Calle:</strong> {{ $event->location_addressStreetAddress }}</p>
                            @endif
                            @if($event->location_addressLocality)
                                <p><strong>Localidad:</strong> {{ $event->location_addressLocality }}</p>
                            @endif
                            @if($event->location_addressRegion)
                                <p><strong>Región:</strong> {{ $event->location_addressRegion }}</p>
                            @endif
                            @if($event->location_postalCode)
                                <p><strong>Código Postal:</strong> {{ $event->location_postalCode }}</p>
                            @endif
                            @if($event->location_addressCountry)
                                <p><strong>País:</strong> {{ $event->location_addressCountry }}</p>
                            @endif
                        </div>
                        <div id="map" style="height: 300px; width: 100%;"></div>
                    </div>


                    <input type="hidden" id="latitude" name="latitude" value="{{ $event->coordinates->latitude }}">
                    <input type="hidden" id="longitude" name="longitude" value="{{ $event->coordinates->longitude }}">
                    <input type="hidden" id="mapzoom" name="mapzoom" value="14">
                    @vite(['resources/js/map.js'])

                    <div class="markdown">
                        {!! $event['summary'] ?? '' !!}
                    </div>



                    <div class="flex flex-wrap mt-4">
                        @foreach ($event->apfiles as $file)
                            @if (explode('/',$file->file_type)[0] == 'image')
                                <img src="{{ $file->getUrlAttribute() }}" class="h-32 object-cover mr-2 mb-2">
                            @endif
                            @if (explode('/',$file->file_type)[0] == 'video')
                                <video controls src="{{ $file->getUrlAttribute() }}" class="h-32 object-cover mr-2 mb-2"></video>
                            @endif
                        @endforeach
                    </div>

                    <div class="markdown mt-4">
                        <hr>
                        {!! $event['content'] ?? '' !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
