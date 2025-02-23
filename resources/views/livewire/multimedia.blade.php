<div>
    <input type="file" wire:model="media" multiple accept="image/*,audio/*,video/*" />
    <input type="hidden" name="uniqid" value="{{$uniqid}}" />
    <div wire:loading wire:target="media">
        Subiendo...
    </div>
    @foreach ($files as $key => $file)
        <div class="flex flex-items">
            <img src="{{ asset('storage/'.$file['path']) }}" class="w-16 h-16" />
            <textarea class="w-full" name="alt_{{$key}}" 
                wire:model.debounce.500ms="files.{{$key}}.alt" 
                wire:change="updateAlt"
                Placeholder="Describa la imagen para las personas con dificultads visuales"></textarea>
            <i class="fas fa-times p-2" wire:click="removeFile({{$key}})"></i>
        </div>
    @endforeach
</div>
