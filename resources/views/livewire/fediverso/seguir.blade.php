<div class="">
    @guest
        <button class="btn btn-primary">Seguir</button>
    @endguest
    @auth
        @if ($siguiendo)
            <button class="btn btn-error btn-sm" wire:click="dejarDeSeguir">Dejar de seguir</button>
        @else           
            <button class="btn btn-primary btn-sm" wire:click="seguir">Seguir</button>
        @endif
    @endif
</div>

