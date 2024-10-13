<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Evento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('eventos.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label for="titulo" class="block text-sm font-medium text-gray-700">Título</label>
                            <input type="text" name="titulo" id="titulo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50" value="{{ old('titulo') }}" />
                            @error('titulo')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea name="descripcion" id="descripcion" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50">{{ old('descripcion') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                            <input type="datetime-local" name="fecha_inicio" id="fecha_inicio"  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50" required value="{{ old('fecha_inicio') }}" />
                        </div>

                        <div class="mb-4">
                            <label for="fecha_fin" class="block text-sm font-medium text-gray-700">Fecha de Fin (opcional)</label>
                            <input type="datetime-local" name="fecha_fin" id="fecha_fin" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50" {{ old('fecha_fin') }} />
                        </div>


                        <livewire:provincia-municipio-selector />

                            @error('municipio_id')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror



                        @if ($equipos->isNotEmpty())
                            <div class="mb-4">
                                <label for="team_id" class="block text-sm font-medium text-gray-700">Publicar en Equipo (opcional)</label>
                                <select name="team_id" id="team_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50">
                                    <option value="">Ninguno</option>
                                    @foreach ($equipos as $equipo)
                                        <option 
                                            @if (old('team_id') == $equipo->id)
                                                selected
                                            @endif
                                        value="{{ $equipo->id }}">{{ $equipo->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label for="cover" class="block text-sm font-medium text-gray-700">Imagen (opcional)</label>
                            <input type="file" name="cover" id="cover" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50" />
                            @error('cover')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror

                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary">
                                Crear Evento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
