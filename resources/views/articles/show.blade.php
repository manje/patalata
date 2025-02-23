<x-app-layout>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="w-full aspect-[3/1] text-white relative">
                    @foreach ($article->apfiles as $file)
                        @if (explode('/',$file->file_type)[0]=='image')
                            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{  $file->getUrlAttribute() }}');"></div>
                            @break
                        @endif
                    @endforeach
                    <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-center px-4">
                        <div class='flex flex-items mt-1'>
                            <img class="rounded-full w-12 h-12 object-cover" src="{{ data_get($article->GetActor(true), 'icon.url', '') }}" alt="{{ $article->GetActor(true)['name'] }}" />
                            <div class="ml-2">
                                <a href="{{ route('articles.show', $article['slug']) }}" class="font-bold text-4xl hover:underline">
                                    {{ $article['name'] }} xxxxx
                                </a>
                                <div>{{ '@' }}{{ $article->GetActor(true)['preferredUsername'] }}</div>
                            </div>
                        </div>
                        <div class="text-white text-sm">
                            <span>{{ \Carbon\Carbon::parse($article['created_at'])->format('d M, H:s') }}</span>
                        </div>
                        <div class='markdown line-clamp-4 truncate w-full md:w-3/5'>{!! $article['summary'] ?? '' !!}</div>
                    </div>

                    <div class="absolute bottom-4 right-4 flex flex-items">
                        @if ($article->editable())
                            <a href="{{ route('articles.edit', $article['slug']) }}" class="btn btn-secondary btn-sm mr-2">Editar</a>
                        @endif
                        <livewire:fediverso.seguir :actor="$article->GetActor()" />
                    </div>                    
                </div>
                <div class="p-2"">
                    <div class="markdown">
                        {!! $article['summary'] ?? ''  !!}
                    </div>
                    <div class="flex flex-wrap">
                        @foreach ($article->apfiles as $file)
                            @if (explode('/',$file->file_type)[0]=='image')
                                <img src="{{ $file->getUrlAttribute() }}" class="h-32 object-cover">
                            @endif
                            @if (explode('/',$file->file_type)[0]=='video')
                                <video src="{{ $file->getUrlAttribute() }}" class="h-32 object-cover"></video>
                            @endif
                        @endforeach
                    </div>
                    <div class="markdown">
                    <hr>
                    {!! $article['content'] ?? '' !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
