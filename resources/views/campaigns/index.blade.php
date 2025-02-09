<x-app-layout>
    <x-slot name="header">
        <!-- titutlo y botón, a la derecha, para añadir una nueva nota -->
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Campañas') }}
            </h2>
            @auth
            <a href="{{ route('campaigns.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Crear Campaña') }}
            </a>
            @endauth
        
        </div>

    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @foreach($campaigns as $campaign)
                        <div class="mb-4">
                            <a href="{{ route('campaigns.show', $campaign->slug) }}" class="text-lg font-bold text-blue-500 hover:underline">
                                {{ $campaign->name }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
