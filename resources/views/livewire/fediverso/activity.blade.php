<div class="
listrespuestas=@if ($activity['type']  != 'Announce')
border-b
@endif
p-2"
wire:init="load" >

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
        @switch($activity['type'])
            @case('Announce')
                <div class="flex text-gray-500">
                    <span>
                        Impulsado por <a href='/{{'@'}}{{ $activity['actor']['userfediverso'] }}'   >{{ $activity['actor']['userfediverso'] }}</a>
                    </span>




                    <div class="ml-14 flex-1 text-right">
                        {{ $activity['published']->diffForHumans() }}
                    </div> 
                </div>
                <div>
                    <livewire:fediverso.activity :activity="$activity['object']"  :diferido="true" :key="$activity['id']" />
                </div>
            @break
            @case('Create')
                <livewire:fediverso.activity :activity="$activity['object']"  :diferido="true"  :key="$activity['object']['id']" />
            @break
            @case('Event')
                <div class="flex items-start space-x-4 @if ($origen) ml-20 @endif">

                    <div class="avatar">
                        <div class="w-12 rounded-full">
                            @if (isset($activity['attributedTo']['icon']['url']))
                            <a href="/{{ "@" }}{{ $activity['attributedTo']['preferredUsername'] }}{{ "@" }}{{ explode("/", $activity['attributedTo']['inbox'])[2] }}" class="text-lg">
                                <img src="{{ $activity['attributedTo']['icon']['url'] }}" alt="Avatar">
                            </a>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h2 class="font-bold text-lg">
                            @if (isset($activity['attributedTo']['name']))
                            <a href="/{{ "@" }}{{ $activity['attributedTo']['preferredUsername'] }}{{ "@" }}{{ explode("/", $activity['attributedTo']['inbox'])[2] }}" class="text-lg">
                                {{ $activity['attributedTo']['name'] }}
                            </a>
                            @endif
                        </h2>
                        <p class="text-sm text-gray-500">
                            <a href="/{{ "@" }}{{ $activity['attributedTo']['preferredUsername'] }}{{ "@" }}{{ explode("/", $activity['attributedTo']['inbox'])[2] }}" class="text-lg">
                                {{ $activity['attributedTo']['preferredUsername'] }}{{ "@" }}{{ explode("/", $activity['attributedTo']['inbox'])[2] }}
                            </a>
                            {{ $activity['published']->diffForHumans() }}
                        </p>
                        <div class="mt-2">
                            <h3 class="text-xl font-semibold">{{ $activity['name'] ?? 'Evento' }}</h3>
                            <p class="mt-1 text-gray-700">
                                {{ $activity['summary'] ?? 'Sin descripción disponible.' }}
                            </p>
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
                            <p class="mt-1 text-sm text-gray-500">
                                <i class="fa-solid fa-link mr-1"></i> 
                                <a href="{{ $activity['url'] }}" target="_blank" class="text-blue-500">Ver en la web</a>
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            @break


            @case('Question')
            <div class="flex items-start space-x-4 @if ($origen) ml-20 @endif">
                <div class="avatar">
                    <div class="w-12 rounded-full">
                        @if (isset($activity['attributedTo']['icon']['url']))
                        <a href="/{{ "@" }}{{ $activity['attributedTo']['preferredUsername'] }}{{ "@" }}{{ explode("/", $activity['attributedTo']['inbox'])[2] }}" class="text-lg">
                            <img src="{{ $activity['attributedTo']['icon']['url'] }}" alt="Avatar">
                        </a>
                        @endif
                    </div>
                </div>

                <div>
                    <h2 class="font-bold text-lg">
                        @if (isset($activity['attributedTo']['name']))
                        <a href="/{{ "@" }}{{ $activity['attributedTo']['preferredUsername'] }}{{ "@" }}{{ explode("/", $activity['attributedTo']['inbox'])[2] }}" class="text-lg">
                            {{ $activity['attributedTo']['name'] }}
                        </a>
                        @endif
                    </h2>
                    <p class="text-sm text-gray-500">
                        <a href="/{{ "@" }}{{ $activity['attributedTo']['preferredUsername'] }}{{ "@" }}{{ explode("/", $activity['attributedTo']['inbox'])[2] }}" class="text-lg">
                            {{ $activity['attributedTo']['preferredUsername'] }}{{ "@" }}{{ explode("/", $activity['attributedTo']['inbox'])[2] }}
                        </a>
                        {{ $activity['published']->diffForHumans() }}
                    </p>
                    <div class="mt-2">
                        <p class="mt-2 text-gray-700">
                            {!! $activity['content'] ?? 'Sin descripción para esta pregunta.' !!}
                        </p>

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

                    @if (isset($activity['endTime']))
                    <p class="mt-4 text-sm text-gray-500">
                        <i class="fa-solid fa-clock mr-1"></i>
                        La votación termina: {{ \Carbon\Carbon::parse($activity['endTime'])->format('d M Y, H:i') }}
                    </p>
                    @endif
                </div>
            </div>
            @break




            @case('Note')
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
                <div class="flex items-start space-x-4 @if ($origen) ml-20 @endif">
                    <div class="avatar">
                        <div class="w-12 rounded-full">
                            @if (isset($activity['attributedTo']['icon']['url']))
                            <a href="/{{"@"}}{{ $activity['attributedTo']['preferredUsername'] }}{{"@"}}{{ explode("/",$activity['attributedTo']['inbox'])[2] }}" class="text-lg">
                                <img src="{{ $activity['attributedTo']['icon']['url'] }}" alt="Avatar">
                            </a>
                            @endif
                        </div>
                    </div>
                    <div>
                        <h2 class="font-bold text-lg">
                            @if (isset($activity['attributedTo']['preferredUsername']))
                            <a href="/{{"@"}}{{ $activity['attributedTo']['preferredUsername'] }}{{"@"}}{{ explode("/",$activity['attributedTo']['inbox'])[2] }}" class="text-lg">
                                {{ $activity['attributedTo']['name'] }}
                            </a>
                            @endif
                        </h2>
                        <p class="text-sm text-gray-500">
                            <a href="/{{"@"}}{{ $activity['attributedTo']['preferredUsername'] }}{{"@"}}{{ explode("/",$activity['attributedTo']['inbox'])[2] }}" class="text-lg">
                                {{ $activity['attributedTo']['preferredUsername']  }}{{"@"}}{{ explode("/",$activity['attributedTo']['inbox'])[2] }}
                            </a>
                                {{ $activity['published']->diffForHumans() }}
                        </p>
                        <p class="mt-2">
                            {!! $activity['content'] ?? 'ERROR: Sin descripción para esta nota ¿?.' !!}

                        </p>
                    </div>
                </div>
            @break
            @default
                Tipo no implementado: {{ $activity['type'] }}
            
        @endswitch
        @if ($activity['type']!='Announce')
        @if (isset($activity['attachment']))
                @foreach ($activity['attachment'] as $media)
                    <div class="mt-2">
                        @if (isset($media['mediaType']))
                            @switch ($media['mediaType'])
                                @case ('image/jpeg')
                                @case ('image/png')
                                @case ('image/gif')
                                @case ('image/svg+xml')
                                @case ('image/webp')
                                    <img src="{{ $media['url'] }}" class="mt-2 w-full border border-gray-300">
                                @break
                                @case ('video/mp4')
                                @case ('video/ogg')
                                @case ('video/webm')
                                    <video src="{{ $media['url'] }}" class="mt-2 w-full border border-gray-300"></video>
                                @break
                                @default
                                {{ $media['mediaType']}}
                                {{ print_r($media)}}
                            @endswitch
                        @endif
                    </div>
                @endforeach
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
            @endif


            <div class="mt-2 flex space-x-4 text-gray-500">
                <button class="flex items-center space-x-1">
                    <i class="fa-regular fa-heart mr-2"></i>
                    @if (isset($activity['num_likes']))
                    @if ($activity['num_likes']!=0)
                        {{ $activity['num_likes']}}
                    @endif
                    @endif
                    <span>Me gusta</span>
                </button>
                <button class="flex items-center space-x-1">
                <i class="fa-solid fa-retweet mr-2"></i>
                    @if (isset($activity['num_shares']))
                    @if ($activity['num_shares']!=0)
                        {{ $activity['num_shares']}}
                    @endif
                    @endif
                    <span>Impulsos</span>
                </button>
                <button class="flex items-center space-x-1"  wire:click="verrespuestas()">
                    <i class="fa-solid fa-reply mr-2"></i>
                        {{ $activity['num_replies']}}
                    <span>Respuestas</span>
                </button>
            </div>
             

            <span wire:target="verrespuestas" wire:loading.delay class="loading loading-ring loading-md"></span>

            @if ($respuestas)
            <div class="ml-14">
                @foreach ($listrespuestas as $res)
                    <livewire:fediverso.activity :activity="$res" :diferido="true" :msgrespondiendo="false"  />  
                @endforeach
            </div>
            @endif
        @endif
    @endif
@endif
</div>

