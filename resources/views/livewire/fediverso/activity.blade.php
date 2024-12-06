
<div class="
@if ($activity['type']  != 'Announce')
border-b
@endif
py-2"
wire:init="load" >


@if ($loading)
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center">
            <p class="mt-20"><span class="loading loading-ring loading-lg"></span> Cargando...</p>
            </div>
        </div>
@else




    @if (isset($activity['error']))
        {{ $activity['error'] }}
    @else
      
        @switch($activity['type'])
            @case('Announce')
                <div class="flex text-gray-500">
                    <span >Rebotado por {{ $activity['actor']['preferredUsername'] }}</span>
                    <div class="ml-14 flex-1 text-right">
                        {{ $activity['published'] }}
                    </div> 
                </div>
                <div>
                    <livewire:fediverso.activity :activity="$activity['object']"   />
                </div>
                @break
          @case('Create')
              <livewire:fediverso.activity :activity="$activity['object']"  :key="$activity['object']['id']" />
              @break
          @case('Note')
                @if (isset($activity['isreply']))
                    @if ($origen)
                            <livewire:fediverso.activity :activity="$activity['isreply']"   />
                    @else                   
                    <div class="">
                        <div class="text-gray-500 font-bold">
                        <a class="cursor-pointer" wire:click="verorigen()"
                        >Respondiendo a {{ $activity['autororigen'] }}</a>
                        </div>
                    </div>
                    @endif
                    
                @endif
                    <div class="flex items-start space-x-4 @if ($origen) ml-20 @endif">


                    <div class="avatar">
                        <div class="w-12 rounded-full">
                            @if (isset($activity['attributedTo']['icon']['url']))
                            <img src="{{ $activity['attributedTo']['icon']['url'] }}" alt="Avatar">
                            @endif
                        </div>
                    </div>
                    <div>
                        <h2 class="font-bold text-lg">
                            <a href="/{{"@"}}{{ $activity['attributedTo']['preferredUsername'] }}{{"@"}}{{ explode("/",$activity['attributedTo']['inbox'])[2] }}" class="text-lg">
                                {{ $activity['attributedTo']['name'] }}
                            </a>
                        </h2>
                        <p class="text-sm text-gray-500">
                            <a href="/{{"@"}}{{ $activity['attributedTo']['preferredUsername'] }}{{"@"}}{{ explode("/",$activity['attributedTo']['inbox'])[2] }}" class="text-lg">
                                {{ $activity['attributedTo']['preferredUsername']  }}{{"@"}}{{ explode("/",$activity['attributedTo']['inbox'])[2] }}
                            </a>
                                {{ $activity['published']->diffForHumans() }}
                        </p>
                        <p class="mt-2">
                        {!! $activity['content'] !!}
                        </p>
                    </div>
                </div>
                <div class="mt-2 flex space-x-4 text-gray-500">
                    <button class="flex items-center space-x-1">
                        <i class="fa-regular fa-heart mr-2"></i>
                        @if (isset($activity['likes']))
                        @if ($activity['likes']!=0)
                            {{ $activity['likes']}}
                        @endif
                        @endif
                        <span>Me gusta</span>
                    </button>
                    <button class="flex items-center space-x-1">
                    <i class="fa-solid fa-retweet mr-2"></i>
                        @if (isset($activity['shares']))
                        @if ($activity['shares']!=0)
                            {{ $activity['shares']}}
                        @endif
                        @endif
                        <span>Retwittear</span>
                    </button>
                    <button class="flex items-center space-x-1">
                        <i class="fa-solid fa-comment mr-2"></i>
                        @if (isset($activity['replies']))
                        @if ($activity['replies']!=0)
                            {{ $activity['replies']}}
                        @endif
                        @endif
                        <span>Comentar</span>
                    </button>
                </div>
              @break
          @default
              Tipo no implementado: {{ $activity['type'] }}
      @endswitch
    @endif
@endif
</div>

