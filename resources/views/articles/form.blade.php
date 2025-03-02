

    <div class="mb-4">
        <x-label for="name" value="{{ __('Titulo del artículo') }}" />
        <x-input id="name" class="block mt-1 w-full" type="text" name="name" value="{{ old('name',$article->name) }}" required autofocus />
    </div>
    @if (isset($equipos))
    <div class="mb-4">
        <label for="team_id" class="block text-sm font-medium text-gray-700">Publicar como</label>
        <select name="team_id" id="team_id" 
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50">
            <option value="">Publicar como {{ Auth::user()->name }}</option>
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
        <x-label for="summary" value="{{ __('Resumen') }}" />
        <x-textarea id="summary" class="block mt-1 w-full" name="summary" required maxlength="250" value="{!! old('summary',$article->summary) !!}" />
    </div>
    <div class="mb-4">
        <x-label for="content" value="{{ __('Texto completo') }}" />
        <x-textarea id="content" class="block mt-1 w-full" name="content" required value="{!! old('content',$article->content) !!}" />
    </div>
    <div class="mb-4">
        <x-label for="content" value="{{ __('Imágenes y Video') }}" />
        @if (old('uniqid',$uniqid ))
            <livewire:multimedia :uniqid=" old('uniqid',$uniqid)" />
        @else
            <livewire:multimedia :files="$article->apfiles->toArray()" :uniqid="$uniqid ?? old('uniqid')" /> 
        @endif
    </div>
    


    <div class="mb-4">
        <x-label for="content" value="{{ __('Asociar a una campaña') }}" />
        @if (old('campaigns', $article->campaigns))
            <livewire:fediverso.sel-campaign :campaigns="old('campaigns', $article->campaigns)" />
        @else
            <livewire:fediverso.sel-campaign />
        @endif
    </div>



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


