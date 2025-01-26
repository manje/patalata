<x-fediverso-layout>

    <div class="w-full max-w-4xl mx-auto text-xl" border-b-2>
        <div class="w-full max-w-6xl mx-auto bg-cover bg-blue-200 bg-center bg-no-repeat aspect-[3/1]" style="
                @if (isset($actor['image']))
                background-image: url('{{ $actor['image']['url'] }}');
                @endif
            ">
        </div>
        <!-- foto de perfil superpuesta la mitad sobre el banner sueprior -->
        <div class="w-full flex -mt-12  pl-6">
            <div class="">
                @if (isset($actor['icon']))
                    <img src="{{ $actor['icon']['url'] }}" alt="foto de perfil" class="h-24 w-24 rounded-full border-4 border-white">
                @endif
            </div>
            <div class="flex-1 text-right mt-12 pr-6 pt-2">
            @if (!$tehabloqueado)
            <livewire:fediverso.seguir :actor="$actor" />
            @else
                Este usuario te ha bloqueado
            @endif
            </div>
        </div>
        <div class="mt-1 pl-6  pb-4 ">
            <div class='font-bold mb-2 text-2xl'>{{ $actor['name'] }}</div>
            {{ '@' }}{{ $actor['preferredUsername'] }} <span class="mt-1 bg-blue-200 p-1 rounded">{{ '@' }}{{ explode("/",$actor['inbox'])[2] }}</span>
        </div>
        @if (isset($actor['summary']))
          <div class="mx-6 border-dashed border-2 rounded-lg text-sm border-gray-200 p-2 bg-blue-50">
            {!! $actor['summary'] !!}
          </div>
        @endif

        <div class="mt-1 pl-6  pb-4 border-b-2 font-bold">
                @if (isset($actor['countfollowing']))
                    @if (is_integer($actor['countfollowing']))
                    <span class=''>{{ number_format($actor['countfollowing'],0,'.','.') }} siguiendo</span>
                    @endif
                @endif
                @if (isset($actor['countfollowers']))
                    @if (is_integer($actor['countfollowers']))
                    <span class='ml-4'>{{ number_format($actor['countfollowers'],0,'.','.') }} seguidores</span>
                    @endif
                @endif
        </div>

        <div class="w-full">
            @auth
                <livewire:fediverso.timeline :actor="$actor" >
            @endauth
        </div>
    </div>
</x-fediverso-layout>
