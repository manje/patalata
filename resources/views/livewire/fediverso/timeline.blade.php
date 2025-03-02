<div wire:init="loadPosts" wire:poll.15s="Nuevas">
    @if ($timeline===false)
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center">
            <p class="mt-20"><span class="loading loading-ring loading-lg"></span> Cargando...</p>
            </div>
        </div>
    @else
        @if (count($timeline)>0)
            @if ($nuevas>0)
            <div class="m-4"  >
                <div role="alert" class="alert cursor-pointer"  wire:click="VerNuevas">
                    <i class="fa fa-bell"></i>
                    <span wire:target="VerNuevas" wire:loading.remove>Hay {{$nuevas}} 
                        @if ($nuevas>1)
                            toots
                        @else
                            toot
                        @endif
                         nuevos.</span>
                    <span wire:target="VerNuevas" wire:loading.delay class="loading loading-ring loading-md"></span>
                </div>
            </div>
            @endif
            @foreach ($timeline as $status)
                @if (isset($status['id']))
                    <livewire:fediverso.activity 
                        :activity="$status" 
                        :diferido="false" 
                        :key="$status['id'] . $serial" 
                    />
                @endif
            @endforeach
        @else
            <div class="flex-1 flex items-center justify-center">
                <div class="text-center">
                <p class="mt-20">No hay publicaciones</p>
                </div>
            </div>
        @endif

    @endif
    <div id=buscandomas style='display:none;'>
        <p class="mt-20"><span class="loading loading-ring loading-lg"></span> Cargando...</p>
    </div>

</div>
