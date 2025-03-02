<div>
    <input type=hidden id=campaigns wire:model="campaigns">

    @if (count($listado)>0)
        <div class="flex flex-items">
        @foreach ($listado as $m)
            <div class="m-2">
                <img class="rounded-full w-12 h-12 object-cover" src="{{ data_get($m, 'icon.url', '') }}" alt="{{ $m['name'] }}" />
                <button type=button wire:click="del('{{ $m['id'] }}','editor')" class='btn btn-xs btn-primary'>Elimiar</button>
            </div>
        @endforeach
        </div>
    @endif

    <x-input class="w-full py-4" id=search name=search wire:model.live.500ms="search" placeholder="Puede asociar este contenido a una campaña que sigas" />
    @if (count($busqueda)>0)
        @foreach ($busqueda as $m)
            <div class="border-b mt-4 p-2">
                <div class="">
                    <x-fediverso.actor :actor="$m" />
                    <button type=button wire:click="add('{{ $m['id'] }}','editor')" class='btn btn-xs btn-primary'>Añadir</button>
                </div>
            </div>
        @endforeach
    @endif

</div>