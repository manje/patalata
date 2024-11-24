<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear post') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Título</label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50" value="{{ old('name') }}" autofocus />
                            
                            @error('name')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="content" class="block text-sm font-medium text-gray-700">Contenido</label>
                            <textarea name="content" id="content" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50">{{ old('content') }}</textarea>
                        </div>
                        <!-- Un post puede estar vinculado a varias categorias, checkboxs para cada categoria -->
                        <div class="mt-4">
                            <label for="categorias" class="block text-sm font-medium text-gray-700">Categorías</label>
                            @foreach ($categories as $categoria)
                                <div>
                                    <input type="checkbox" name="categorias[]" id="categoria{{ $categoria->id }}" value="{{ $categoria->id }}" class="rounded-md shadow-sm focus:ring focus:ring-opacity-50" @if(is_array(old('categorias')) && in_array($categoria->id, old('categorias'))) checked @endif>
                                    <label for="categoria{{ $categoria->id }}">{{ $categoria->nombre }}</label>
                                </div>
                            @endforeach
                        </div>

                        <livewire:provincia-municipio-selector :reqired="true" :selectedMunicipio="old('municipio_id')" :selectedProvincia="old('provincia_id')" />

                           


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
                                Crear post
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
