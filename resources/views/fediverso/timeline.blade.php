<div wire:init="loadPosts" >
    @if ($timeline)
        @foreach ($timeline as $status)
            <livewire:fediverso.activity :activity="$status" :key="$status->id" />
        @endforeach
    @endif
</div>


