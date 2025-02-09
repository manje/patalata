<div class='flex flex-items'>
    @if ($tempPath)
        <img src="{{ asset("storage/$tempPath") }}" class="rounded-lg w-16 h-16 object-cover mr-4">
    @endif
    <div class='w-full items-center justify-center'>
        <input type="hidden" wire:model="tempPath" id='{{ $name }}' name='{{ $name }}' value="{{$tempPath}}">
        <input type="file" wire:model="image" id='{{ $name }}_storage' name='{{ $name }}_storage'>
        @error('{{ $name }}') <span class="text-red-500">{{ $message }}</span> @enderror
    </div>
</div>
