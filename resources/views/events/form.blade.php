<div class="mb-4">
    <x-label for="name" value="{{ __('Título del evento') }}" />
    <x-input id="name" class="block mt-1 w-full" type="text" name="name" value="{{ old('name', $event->name) }}" required autofocus />
</div>

@if (isset($equipos))
    <div class="mb-4">
        <label for="team_id" class="block text-sm font-medium text-gray-700">Publicar como</label>
        <select name="team_id" id="team_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50">
            <option value="">Publicar como {{ Auth::user()->name }}</option>
            @foreach ($equipos as $equipo)
                <option value="{{ $equipo->id }}" @if (old('team_id', $event->team_id) == $equipo->id) selected @endif>
                    {{ $equipo->name }}
                </option>
            @endforeach
        </select>
    </div>
@endif

<div class="mb-4">
    <x-label for="summary" value="{{ __('Resumen') }}" />
    <x-textarea id="summary" class="block mt-1 w-full" name="summary" required maxlength="250" value="{!! old('summary', $event->summary) !!}" />
</div>

<div class="mb-4">
    <x-label for="content" value="{{ __('Texto completo') }}" />
    <x-textarea id="content" class="block mt-1 w-full" name="content" value="{!! old('content', $event->content) !!}" />
</div>

<div class="mb-4 w-full flex flex-items">
<div class="mr-2 w-full">
    <x-label for="startTime" value="{{ __('Fecha de inicio') }}" />
    <x-input id="startTime" class="block mt-1 w-full" type="datetime-local" name="startTime" value="{{ old('startTime', $event->startTime) }}" required />
</div>

<div class="ml-2 w-full">
    <x-label for="endTime" value="{{ __('Fecha de finalización (opcional)') }}" />
    <x-input id="endTime" class="block mt-1 w-full" type="datetime-local" name="endTime" value="{{ old('endTime', $event->endTime) }}" />
</div>
</div>


<div class="mb-4">
    <x-label for="location" value="Ubicación en el mapa" />
    <div id="map" style="height: 300px; width: 100%;"></div>
    <p>Latitud: <span id="latText">{{ old('latitude', $event->latitude ?? 'No seleccionada') }}</span></p>
    <p>Longitud: <span id="lngText">{{ old('longitude', $event->longitude ?? 'No seleccionada') }}</span></p>
</div>
<input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $event->latitude ?? '') }}">
<input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $event->longitude ?? '') }}">

@vite(['resources/js/map.js'])


<div class="mb-4">
    <x-label for="location_name" value="{{ __('Nombre del lugar') }}" />
    <x-input id="location_name" class="block mt-1 w-full" type="text" name="location_name" value="{{ old('location_name', $event->location_name) }}" />
</div>

<div class="mb-4">
    <x-label for="location_streetAddress" value="{{ __('Dirección') }}" />
    <x-input id="location_streetAddress" class="block mt-1 w-full" type="text" name="location_streetAddress" value="{{ old('location_streetAddress', $event->location_streetAddress) }}" />
</div>

<div class="mb-4">
    <x-label for="location_postalCode" value="{{ __('Código Postal') }}" />
    <x-input id="location_postalCode" class="block mt-1 w-full" type="text" name="location_postalCode" value="{{ old('location_postalCode', $event->location_postalCode) }}" />
</div>

<div class="mb-4">
    <x-label for="location_addressLocality" value="{{ __('Localidad') }}" />
    <x-input id="location_addressLocality" class="block mt-1 w-full" type="text" name="location_addressLocality" value="{{ old('location_addressLocality', $event->location_addressLocality) }}" />
</div>

<div class="mb-4">
    <x-label for="location_addressRegion" value="{{ __('Regió0n') }}" />
    <x-input id="location_addressRegion" class="block mt-1 w-full" type="text" name="location_addressRegion" value="{{ old('location_addressRegion', $event->location_addressRegion) }}" />
</div>

<div class="mb-4">
    <x-label for="location_addressCountry" value="{{ __('País') }}" />
    <x-input id="location_addressCountry" class="block mt-1 w-full" type="text" name="location_addressCountry" value="{{ old('location_addressCountry', $event->location_addressCountry) }}" />
</div>

<div class="mb-4">
    <x-label for="content" value="{{ __('Imágenes y Video') }}" />
    @if (old('uniqid', $uniqid))
        <livewire:multimedia :uniqid="old('uniqid', $uniqid)" />
    @else
        <livewire:multimedia :files="$event->apfiles->toArray()" :uniqid="$uniqid ?? old('uniqid')" />
    @endif
</div>

<div class="mb-4">
    <label for="place_id" class="block text-sm font-medium text-gray-700">Localidad (opcional)</label>
    <select name="place_id" id="place_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50">
        <option value="">Seleccione una localidad</option>
        @foreach ($places as $place)
            <option value="{{ $place->id }}" @if (old('place_id', $event->place_id) == $place->id) selected @endif>
                {{ $place->name }}
            </option>
        @endforeach
    </select>
    <span>Puede elegir una localidad diferente al evento si se trata de una actividad organizada por un colectivo de esa localidad</span>
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


