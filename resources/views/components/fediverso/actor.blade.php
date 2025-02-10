@props(['actor' => false])

<div class='flex flex-items'>
    <img class="rounded-full w-12 h-12 object-cover" src="{{ data_get($actor, 'icon.url', '') }}" alt="{{ $actor['name'] }}" />
    <div class="ml-2">
        <a href="/{{ '@' }}{{ $actor['userfediverso']  }}" class="font-bold text-xl hover:underline">
            {{ $actor['name'] }}
        </a>
        <div>{{ '@' }}{{ $actor['userfediverso']  }}</div>
    </div>
</div>
