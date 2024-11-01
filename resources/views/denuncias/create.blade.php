<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Denuncia') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
<form method="POST" action="{{ route('denuncias.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="mb-4">
        <label for="titulo" class="block text-sm font-medium text-gray-700">Título</label>
        <input type="text" name="titulo" id="titulo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50" value="{{ old('titulo') }}" autofocus />
        
        @error('titulo')
            <span class="text-sm text-red-600">{{ $message }}</span>
        @enderror
    </div>

    <div class="mb-4">
        <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
        <textarea name="descripcion" id="descripcion" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50">{{ old('descripcion') }}</textarea>
    </div>

    <!-- Localidad (Provincia y Municipio) Selector -->
    <livewire:provincia-municipio-selector :required="true" :selectedMunicipio="old('municipio_id')" :selectedProvincia="old('provincia_id')" />
    @error('municipio_id')
        <span class="text-sm text-red-600">{{ $message }}</span>
    @enderror

    <!-- Publicar en Equipo (opcional) -->
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

    <!-- Categoría -->
    <div class="mt-4">
        <label for="categories" class="block text-sm font-medium text-gray-700">Categorías</label>
        @foreach ($categories as $category)
            <div>
                <input type="checkbox" name="categories[]" id="category{{ $category->id }}" value="{{ $category->id }}" class="rounded-md shadow-sm focus:ring focus:ring-opacity-50" @if(is_array(old('categories')) && in_array($category->id, old('categories'))) checked @endif>
                <label for="category{{ $category->id }}">{{ $category->nombre }}</label>
            </div>
        @endforeach
    </div>

    <!-- Imagen de Cover -->
    <div class="mb-4">
        <label for="cover" class="block text-sm font-medium text-gray-700">Imagen de Portada (opcional)</label>
        <input type="file" name="cover" id="cover" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50" />
        @error('cover')
            <span class="text-sm text-red-600">{{ $message }}</span>
        @enderror
    </div>


    <!-- Archivos Adicionales Dinámicos -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Archivos Adicionales (imágenes, videos, audios)</label>
        <div id="fileInputsContainer">
            <input type="file" name="archivos[]" class="archivo-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50" accept="image/*,video/*,audio/*" onchange="addNewFileInput(this)">
        </div>
        @error('archivos.*')
            <span class="text-sm text-red-600">{{ $message }}</span>
        @enderror
    </div>



    <div>
        <button type="submit" class="btn btn-primary">
            Crear Denuncia
        </button>
    </div>
</form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


<script>
    function addNewFileInput(element) {
        // Verifica si se seleccionó un archivo
        if (element.files.length > 0) {
            // Crea un nuevo input de tipo file
            const newFileInput = document.createElement('input');
            newFileInput.type = 'file';
            newFileInput.name = 'archivos[]';
            newFileInput.className = 'archivo-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50';
            newFileInput.accept = 'image/*,video/*,audio/*';
            newFileInput.onchange = function () {
                addNewFileInput(newFileInput);
            };

            // Añade el nuevo input al contenedor de inputs
            document.getElementById('fileInputsContainer').appendChild(newFileInput);
        }
    }
</script>