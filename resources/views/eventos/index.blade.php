<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Agenda de Eventos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (auth()->check())
                        <a href="{{ route('eventos.create') }}" class="btn btn-primary mb-4">
                            Añadir Evento
                        </a>
                    @endif
                    <ul>
                        @foreach ($eventos as $evento)
                            <li class="mb-4">
                                <a href="{{ route('eventos.show', $evento->slug) }}" class="text-blue-600 hover:underline">
                                    {{ $evento->titulo }}
                                </a>
                                <div>
                                    <strong>Organizador:</strong>
                                    {{ $evento->equipo ? $evento->equipo->nombre : $evento->creador->name }}
                                </div>
                                <div>
                                    <strong>Fecha:</strong> {{ $evento->fecha_inicio }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
