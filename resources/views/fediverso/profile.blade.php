<x-fediverso-layout>

    <div class="w-full max-w-4xl mx-auto text-xl">
        <div class="w-full max-w-6xl mx-auto bg-cover bg-blue-200 bg-center bg-no-repeat h-40" style="
                @if (isset($actor['image']))
                background-image: url('{{ $actor['image']['url'] }}');
                @endif
            ">
            
        </div>
        <!-- foto de perfil superpuesta la mitad sobre el banner sueprior -->
        <div class="w-full flex  -mt-12 pl-6">
            <div>
                    <img src="{{ $actor['icon']['url'] }}" alt="foto de perfil" class="h-24 w-24 rounded-full border-4 border-white">
            </div>
            <div class="flex-1 text-right mt-12 pr-6 pt-2">
            <livewire:fediverso.seguir :actor="$actor" />
            </div>
        </div>
        <div class="mt-1 ml-6  border-b-2 pb-4">
            <strong>{{ $actor['name'] }}</strong>
            <br>            
            {{ $actor['preferredUsername'] }}@<span class="ml-1 bg-blue-200 p-2 rounded">{{ explode("/",$actor['inbox'])[2] }}</span>
        </div>
        <div>
            @if ($outbox)
                @foreach ($outbox as $activity)
                    <livewire:fediverso.activity :activity="$activity" :key="$activity['id']" />
                @endforeach
            @else
                <p>No hay actividades</p>
            @endif
        </div>
    </div>
</x-fediverso-layout>
