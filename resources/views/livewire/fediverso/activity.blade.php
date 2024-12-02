<div class="text-gray-700 border-b-2 p-2 text-l">
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
                <livewire:fediverso.activity :activity="$activity['object']"   />
              @break
          @case('Create')
              <livewire:fediverso.activity :activity="$activity['object']"  :key="$activity['object']['id']" />
              @break
          @case('Note')
              <div class="flex">
                <div class="flex">
                  <img src="{{ $activity['attributedTo']['icon']['url'] }}" alt="foto de perfil" class="h-12 w-12 rounded-full border-4 border-white">
                 {{ $activity['attributedTo']['preferredUsername']  }}{{"@"}}{{ explode("/",$activity['attributedTo']['inbox'])[2] }}
                </div>
                <div class="ml-14 flex-1 text-right">
                    {{ $activity['published'] }}
                </div> 


              </div>
              <div class="ml-14">
                  {{ htmlspecialchars($activity['content']) }}
              
              </div>
              @break
          @default
              Tipo no implementado: {{ $activity['type'] }}
      @endswitch
    @endif
</div>

