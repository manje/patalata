

    <div class="mb-4">
        <x-label for="name" value="{{ __('Nombre de la Campaña') }}" />
        <x-input id="name" class="block mt-1 w-full" type="text" name="name" value="{{ old('name',$campaign->name) }}" required autofocus />
    </div>
    @if (isset($equipos))
    <div class="mb-4">
        <x-label for="slug" value="{{ __('Identidad en el Fediverso') }}" />
        <div class="flex flex-items">
            <div class='text-xl px-1 pt-1 border mt-1'>@</div>
            <x-input id="slug" class="block mt-1 w-full" type="text" name="slug" :value="old('slug')" required />
        </div>
    </div>
    @endif


    <div class="mb-4">
        <x-label for="profile_image" value="{{ __('Imagen de perfil') }}" />
        <livewire:file-upload :name="'profile_image'" :old="$campaign->profile_image" :key='"imagenperfil"' />
    </div>
    <div class="mb-4">
        <x-label for="image" value="{{ __('Imagen de cabecera') }}" />
        <livewire:file-upload :name="'image'" :old="$campaign->image" :key='"imagenfondo"' />
    </div>
    <div class="mb-4">
        <x-label for="summary" value="{{ __('Resumen') }} (250 carácteres)" />
        <x-textarea id="summary" class="block mt-1 w-full" name="summary" required maxlength="250" value="{!! old('summary',$campaign->summary) !!}" />
    </div>
    <div class="mb-4">
        <x-label for="content" value="{{ __('Texto completo') }}" />
        <x-textarea id="content" class="block mt-1 w-full" name="content" required value="{!! old('content',$campaign->content) !!}" />
    </div>
    @if (isset($equipos)))
    <div class="mb-4">
        <label for="team_id" class="block text-sm font-medium text-gray-700">Creador de la campaña</label>
        <select name="team_id" id="team_id" 
            required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50">
            <option value="">Seleccione un equipo</option>
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
        <label for="place_id" class="block text-sm font-medium text-gray-700">Localidad (opcional)</label>
        <select name="place_id" id="place_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50">
            <option value="">Seleccione una localidad</option>
            @foreach ($places as $place)
                <option value="{{ $place->id }}"
                    @if (old('place_id') == $place->id)
                        selected
                    @endif
                >{{ $place->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Categorías -->
    <div class="mt-4">
        <label for="categories" class="block text-sm font-medium text-gray-700">Categorías</label>
        @foreach ($categories as $category)
            <div>
                <input type="checkbox" name="categories[]" id="category{{ $category->id }}" value="{{ $category->id }}" class="rounded-md shadow-sm focus:ring focus:ring-opacity-50" @if(is_array(old('categories')) && in_array($category->id, old('categories'))) checked @endif>
                <label for="category{{ $category->id }}">{{ $category->nombre }}</label>
            </div>
        @endforeach
    </div>


