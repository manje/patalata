<div class="
listrespuestas=@if ($activity['type']  != 'Announce')
border-b
@endif
p-2"
wire:init="load" 
 x-data=" { sensitive: {{ $activity['sensitive'] ? 'true' : 'false' }} ,  oculto: {{ $activity['sensitive'] ? 'true' : 'false' }} }" >

@if ($loading)
    <div class="flex-1 flex items-center justify-center mt-2">
        <div class="text-center">
        <p class="mt-2"><span class="loading loading-ring loading-lg"></span> Cargando...</p>
        </div>
    </div>
@else
    @if (isset($activity['error']))
    <pre>
        {{ $activity['error'] }}
    </pre>
    @else
        @if ($activity['type']=='Announce')
            <div class="flex text-gray-500 mx-2">
                <span>
                    Impulsado por <a href='/{{'@'}}{{ $activity['actor']['userfediverso'] }}'   >{{ $activity['actor']['userfediverso'] }}</a>
                </span>
                <div class="ml-14 flex-1 text-right">
                    {{ $activity['published']->diffForHumans() }}
                </div> 
            </div>
            <div>
                <livewire:fediverso.activity :activity="$activity['object']"  :diferido="false" :key="$activity['id']" />
            </div>
        @else
            @if (isset($activity['isreply']))
                @if ($origen)
                        <livewire:fediverso.activity :activity="$activity['isreply']"   />
                @else
                    @if ($msgrespondiendo)                   
                        <div class="">
                            <div class="text-gray-500 font-bold">
                            <a class="cursor-pointer" wire:click="verorigen()"
                            >Respondiendo a {{ $activity['autororigen'] }}</a>
                            </div>
                        </div>
                    @endif
                @endif
            @endif

            <div class="w-full flex items-start space-x-4 @if ($origen) ml-20 @endif">
                <div class="avatar">
                    <div class="w-12 rounded-full">
                        @if (isset($activity['attributedTo']['icon']['url']))
                        <a href="/{{"@"}}{{ $activity['attributedTo']['preferredUsername'] }}{{"@"}}{{ explode("/",$activity['attributedTo']['inbox'])[2] }}" class="text-lg">
                            <img src="{{ $activity['attributedTo']['icon']['url'] }}" alt="Avatar">
                        </a>
                        @endif
                    </div>
                </div>
                <div class="w-full">


                    <div class="flex justify-between items-center w-full">
                        <!-- Div alineado a la izquierda -->
                        <div class="flex items-center">
                            <h2 class="font-bold text-lg">
                                @if (isset($activity['attributedTo']['preferredUsername']))
                                <a href="/{{"@"}}{{ $activity['attributedTo']['preferredUsername'] }}{{"@"}}{{ explode("/",$activity['attributedTo']['inbox'])[2] }}" class="text-lg">
                                    {{ $activity['attributedTo']['name'] ?? '???' }}
                                </a>
                                @endif
                            </h2>
                        </div>

                        <!-- Div alineado a la derecha con el globo si es publico, el candado si es solo seguidores y la arroba si es una nota privada -->
                        <div class="flex items-center text-gray-500">
                            @switch ($activity['visible'])
                                @case ('public')
                                        <i class="fa-solid fa-globe"></i>
                                    @break
                                @case ('followers')
                                        <i class="fa-solid fa-lock"></i>
                                    @break
                                @case ('private')
                                        <i class="fa-solid fa-lock"></i>
                                    @break
                                @default
                            @endswitch
                        </div>
                    </div>


                    <p class="text-sm text-gray-500">
                        <a href="/{{"@"}}{{ $activity['attributedTo']['preferredUsername'] }}{{"@"}}{{ explode("/",$activity['attributedTo']['inbox'])[2] }}" class="text-lg">
                            {{ $activity['attributedTo']['preferredUsername']  }}{{"@"}}{{ explode("/",$activity['attributedTo']['inbox'])[2] }}
                        </a>
                            {{ $activity['published']->diffForHumans() }}
                    </p>

                    @if ($activity['sensitive'])
                        <div x-show="sensitive" x-on:click="oculto = !oculto"
                                class="flex flex-items border border-4 border-yellow-300 bg-black text-white w-full cursor-pointer">





                                <div class="p-1 bg-blue text-center w-full">
                                <i class="text-yellow-500 p-1 fa-solid fa-triangle-exclamation"></i>
                                {{ $activity['summary'] }}
                                <i class="text-yellow-500 p-1 fa-solid fa-triangle-exclamation"></i>
                            </div>
                        </div>
                    @endif
                    <div x-show="!oculto" translate="yes" >                            
                        @if (isset($activity['name']))
                        <h3 class="text-xl font-semibold">{{ $activity['name'] ?? '' }}</h3>
                        @endif
                        @if ($activity['type']!='Note')
                            <p class="mt-1 text-gray-700">
                                {!! $activity['summary'] ?? 'Sin descripción disponible.' !!}
                            </p>
                        @else
                            <p class="mt-1 text-gray-700">
                                {!! $activity['content'] ?? 'Sin contenido disponible.' !!}
                            </p>
                        @endif
                        {{-- Event --}}
                        @if (isset($activity['location']))
                        <p class="mt-2 text-sm text-gray-500">
                            <i class="fa-solid fa-location-dot mr-1"></i> 
                            {{ $activity['location']['name'] ?? $activity['location']['address'] ?? 'Ubicación no especificada' }}
                        </p>
                        @endif
                        @if (isset($activity['startTime']))
                        <p class="mt-1 text-sm text-gray-500">
                            <i class="fa-solid fa-calendar-alt mr-1"></i> 
                            Empieza: {{ \Carbon\Carbon::parse($activity['startTime'])->format('d M Y, H:i') }}
                        </p>
                        @endif
                        @if (isset($activity['endTime']))
                        <p class="mt-1 text-sm text-gray-500">
                            <i class="fa-solid fa-calendar-check mr-1"></i> 
                            Termina: {{ \Carbon\Carbon::parse($activity['endTime'])->format('d M Y, H:i') }}
                        </p>
                        @endif
                        @if (isset($activity['url']))
                        @if ($activity['type']!='Note')
                        <p class="mt-1 text-sm text-gray-500">
                            <i class="fa-solid fa-link mr-1"></i> 
                            <a href="{{ $activity['url'] }}" target="_blank" class="text-blue-500">Ver en la web</a>
                        </p>
                        @endif
                        @endif


                        {{-- Question --}}
                        @if (isset($activity['oneOf']) && is_array($activity['oneOf']))
                        <div class="mt-4">
                            <h4 class="font-bold text-md mb-2">Opciones:</h4>
                            <ul class="list-disc pl-5 text-gray-600">
                                @foreach ($activity['oneOf'] as $option)
                                <li>
                                    {{ $option['name'] ?? 'Opción sin nombre' }}
                                    @if (isset($option['replies']))
                                    @if (isset($option['totalItems']))
                                    <span class="text-sm text-gray-500 ml-2">({{ $option['votes']['totalItems'] }} votos)</span>
                                    @endif
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>











                    <div x-data="{ activeIndex: 0, images: {{ json_encode($activity['attachment'] ?? []) }} }" class="relative w-full">
                        @if (!empty($activity['attachment']))
                            <!-- Imagen o video actual -->
                            <template x-if="images.length > 0">
                                <div>
                                    <template x-for="(media, index) in images" :key="index">
                                        <div x-show="activeIndex === index">
                                            <template x-if="['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'].includes(media.mediaType)">
                                                <img :src="media.url" class="mt-2 w-full border border-gray-300 rounded-lg">
                                            </template>
                                            <template x-if="['video/mp4', 'video/ogg', 'video/webm'].includes(media.mediaType)">
                                                <video :src="media.url" class="mt-2 w-full border border-gray-300 rounded-lg" controls></video>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Botones de navegación -->
                            <div class="absolute inset-0 flex justify-between items-center">
                                <!-- Botón Anterior -->
                                <button 
                                    x-show="images.length > 1"
                                    @click="activeIndex = (activeIndex > 0) ? activeIndex - 1 : images.length - 1"
                                    class="bg-black bg-opacity-50 text-white p-2 rounded-full ml-2">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </buton>

                                <!-- Botón Siguiente -->
                                <button 
                                    x-show="images.length > 1"
                                    @click="activeIndex = (activeIndex < images.length - 1) ? activeIndex + 1 : 0"
                                    class="bg-black bg-opacity-50 text-white p-2 rounded-full mr-2">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>

                            <!-- Indicadores -->
                            <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex space-x-1">
                                <template x-for="(media, index) in images" :key="index">
                                    <div 
                                        @click="activeIndex = index"
                                        :class="activeIndex === index ? 'bg-white' : 'bg-gray-400'"
                                        class="w-2 h-2 rounded-full cursor-pointer">
                                    </div>
                                </template>
                            </div>
                        @endif
                    </div>







                    @if (isset($activity['tag']))
                        @if (count($activity['tag'])>0)
                        <div class="mt-2">
                        @foreach ($activity['tag'] as $tag)
                            @if ($tag['type']=='Hashtag')
                            <span class="bg-gray-100 text-gray-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">{{ $tag['name'] }}</span>
                            @endif
                        @endforeach
                        </div>
                        @endif
                    @endif
                </div>
            </div>
            <div>
                <div class="mt-2 pb-2 flex space-x-4 text-gray-500 border-b ">
                    <button class="flex items-center space-x-1" wire:click="setlike()">
                        <i class="
                        @if ($like)
                            fa-solid text-blue-500
                        @else
                            fa-regular
                        @endif
                        fa-heart mr-2 text-blue"></i>
                        @if (isset($activity['num_likes']))
                        @if ($activity['num_likes']!=0)
                            {{ $activity['num_likes']}}
                        @endif
                        @endif
                        <span>Me gusta</span>
                    </button>
                    @if ($activity['visible']=='public')
                        <button class="flex items-center space-x-1" wire:click="setimpulso()">
                        <i class="
                            @if ($rt)
                                fa-solid text-blue-500
                            @else
                                fa-regular
                            @endif
                            fa-solid fa-retweet mr-2"></i>
                            @if (isset($activity['num_shares']))
                            @if ($activity['num_shares']!=0)
                                {{ $activity['num_shares']}}
                            @endif
                            @endif
                            <span>Impulsos</span>
                        </button>
                    @else
                    {{-- candado --}}
                    <i class="fa-solid fa-lock mr-2 text-gray-300"></i>
                    <span>Impulsos</span>
                    @endif
                    <i class="fa-solid fa-reply mr-2" wire:click="responder('{{ $activity['id'] }}')"></i>
                    <button class="flex items-center space-x-1"  wire:click="verrespuestas()">
                            {{ $activity['num_replies']}}
                        <span class='ml-1'>Respuestas</span>
                    </button>
                </div>
                <div wire:target="verrespuestas" wire:loading.delay class="w-full text-center">
                    <span  class="loading loading-ring loading-md"></span>
                </div>

                @if ($respuestas)
                <div class="ml-14">
                    @foreach ($listrespuestas as $res)
                        <livewire:fediverso.activity :activity="$res" :diferido="true" :msgrespondiendo="false" :key="$res" />  
                    @endforeach
                </div>
                @endif
            </div>
        @endif
    @endif
@endif
</div>

