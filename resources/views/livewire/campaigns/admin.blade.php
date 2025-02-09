<div class="p-4">
    <div class="py-4">
        <p>
            Los administradores son los únicos que pueden gestionar los miembros de una campaña.
        </p><p>
            Todos miembros de los equipos que sean editores de la campaña pueden vincular contenidos de sus equipo a la campaña,
            así como gestinar (rechazar o aceptar) los intentos de vinculación de contenidos de equipos que no sean editores.
        </p>
    </div>
    <div class="flex justify-between">
        <div class="w-full border-x">
            <h2 class="font-bold p-2 text-xl border-y">Administradores</h2>
            @foreach ($list['admin'] as $m)
                <div class="border-b">
                    <div class="m-2"><x-fediverso.actor :actor="$m" /></div>
                    <div class='text-right p-2'>
                        <button wire:click="SetRol('{{ $m['id'] }}','editor')" class='btn btn-xs btn-primary'>Editor</button>
                        <button wire:click="BorrarMiembro('{{ $m['id'] }}')" class='btn btn-xs btn-primary'>Borrar</button>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="w-full border-x">
            <h2 class="font-bold p-2 text-xl border-y">Editores</h2>
            @foreach ($list['editor'] as $m)
                <div class="border-b">
                    <div class="m-2"><x-fediverso.actor :actor="$m" /></div>
                    <div class='text-right p-2'>
                        <button wire:click="SetRol('{{ $m['id'] }}','admin')" class='btn btn-xs btn-primary'>Administrador</button>
                        <button wire:click="BorrarMiembro('{{ $m['id'] }}')" class='btn btn-xs btn-primary'>Borrar</button>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="w-full border-x">
            <h2 class="font-bold p-2 text-xl border-y">Solicitudes</h2>
            @foreach ($list['Join'] as $m)
                <div class="border-b">
                    <div class="m-2"><x-fediverso.actor :actor="$m" /></div>
                    <div class='text-right p-2 flex flex-items'>
                        <div class='w-full text-left text-xs'>{{ $m['created_at']->diffForHumans() }}</div>
                        <div class='w-full'><button wire:click="SetRol('{{ $m['id'] }}','editor')" class='btn btn-xs btn-primary'>Aceptar</button>
                        <button wire:click="BorrarMiembro('{{ $m['id'] }}')" class='btn btn-xs btn-primary'>Rechazar</button></div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="w-full border-x">
            <h2 class="font-bold p-2 text-xl border-y">Invitaciones</h2>
            <x-input name=busca  wire:model.live.debounce.500ms="busca" id="busca" class="w-full" Placeholder="user@instancia"/>
            <span wire:target="Buscar" wire:loading.delay class="loading loading-ring loading-md"></span>
            <div wire:target="Buscar" wire:loading.remove>
                @if ($invitado)
                    <div class="m-2"><x-fediverso.actor :actor="$invitado" /></div>
                    <div class='text-right p-2 border-b'>
                        <button wire:click="Invitar('{{ $invitado['id'] }}')" class='btn btn-xs btn-primary'>Invitar</button>
                    </div>
                @endif
            </div>
            @foreach ($list['Invite'] as $m)
                <div class="border-b">
                    <div class="m-2"><x-fediverso.actor :actor="$m" /></div>
                    <div class='text-right p-2 flex flex-items'>
                        <div class='w-full text-left text-xs'>{{ $m['created_at']->diffForHumans() }}</div>
                        <div><button wire:click="BorrarMiembro('{{ $m['id'] }}')" class='btn btn-xs btn-primary'>Borrar</button></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
