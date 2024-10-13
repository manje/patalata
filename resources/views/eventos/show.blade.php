<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $evento->titulo }}
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-lg font-bold">{{ $evento->titulo }}</h2>
                    <p>{{ $evento->descripcion }}</p>
                    <p><strong>Fecha Inicio:</strong> {{ $evento->fecha_inicio }}</p>
                    <p><strong>Fecha Fin:</strong> {{ $evento->fecha_fin }}</p>
                    <p><strong>Municipio:</strong> {{ $evento->municipio->nombre }}</p>
                    <p><strong>Creador:</strong> {{ $evento->equipo ? $evento->equipo->nombre : $evento->creador->name }}</p>
                    @if($evento->cover)
                        <img src="{{ asset('storage/' . $evento->cover) }}" alt="{{ $evento->titulo }}" class="mt-4">
{{ asset('storage/' . $evento->cover) }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
