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
                    <p><strong>Fecha Inicio:</strong> {{ date('d', strtotime($evento->fecha_inicio)) }} de {{ date('F', strtotime($evento->fecha_inicio)) }} de {{ date('Y', strtotime($evento->fecha_inicio)) }}  {{ date('H', strtotime($evento->fecha_inicio)) }}:{{ date('i', strtotime($evento->fecha_inicio)) }}</p>
                   
                    @if ($evento->fecha_fin)
                        <p><strong>Fecha Fin:</strong> {{ $evento->fecha_fin }}</p>
                    @endif
                    <p><strong>Localidad:</strong> {{ $evento->municipio->nombre }} ({{ $evento->municipio->provincia }})</p>
                    <p>{{ $evento->descripcion }}</p>
                    <p><strong>Creador:</strong> {{ $evento->equipo ? $evento->equipo->name : $evento->creador->name }}</p>
                    @if($evento->cover)
                        <img src="{{ asset('storage/' . $evento->cover) }}" alt="{{ $evento->titulo }}" class="mt-4 w-full">
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
