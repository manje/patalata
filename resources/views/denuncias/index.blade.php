<x-app-layout>
    <x-slot name="header">
        <!-- titutlo y botón, a la derecha, para añadir una nueva denuncia -->
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Denuncias') }}
            </h2>
            @auth
            <a href="{{ route('denuncias.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Crear Denuncia') }}
            </a>
            @endauth
        
        </div>

    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @foreach($denuncias as $denuncia)
                        <div class="mb-4">
                            <a href="{{ route('denuncias.show', $denuncia->slug) }}" class="text-lg font-bold text-blue-500 hover:underline">
                                {{ $denuncia->titulo }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
