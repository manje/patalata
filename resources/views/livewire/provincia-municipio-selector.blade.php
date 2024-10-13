<div>
    <div class="mb-4">
        <label for="{{ $provinciaName }}" class="block text-sm font-medium text-gray-700">Provincia</label>
        <!-- para llamar a un evento si cambia este select -->
        <select  wire:change="cambio" wire:model="selectedProvincia" id="{{ $provinciaName }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50" name="{{ $provinciaName }}">
            <option value="">Seleccione una provincia</option>
            @foreach ($provincias as $key => $provincia)
                <option value="{{ $key }}">{{ $provincia }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-4">
        <label for="{{ $municipioName }}" class="block text-sm font-medium text-gray-700">Municipio</label>
        <select wire:model="selectedMunicipio" id="{{ $municipioName }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50" {{ empty($municipios) ? 'disabled' : '' }} name="{{ $municipioName }}"
        	@if ($required)
        		required
			@endif
        >
            <option value="">Seleccione un municipio</option>
            @foreach ($municipios as $municipio)
                <option value="{{ $municipio->id }}">{{ $municipio->nombre }}</option>
            @endforeach
        </select>
    

    </div>
</div>
