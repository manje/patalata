<div wire:init="loadPosts" >





    @if ($timeline)
        @foreach ($timeline as $status)
            @if (isset($status['id']))
            <livewire:fediverso.activity :activity="$status" :key="$status['id']" />
            @endif
        @endforeach

    @else
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center">
            <p class="mt-20"><span class="loading loading-ring loading-lg"></span> Cargando...</p>
            </div>
        </div>
    @endif
</div>
