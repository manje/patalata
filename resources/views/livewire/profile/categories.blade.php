<x-form-section submit="updateMunAndInt">
    <x-slot name="title">
        Perfil en {{ config('app.name') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Modifique su localidad y/o seleccione sus intereses.') }}
    </x-slot>
    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            @livewire('provincia-municipio-selector', ['provinciaName' => 'provincia_id', 'municipioName' => 'municipio_id', 'selectedProvincia' => Auth::user()->municipio->cpro, 'selectedMunicipio' => Auth::user()->municipio_id, 'required' => true])
        </div>
        <div class="col-span-6 sm:col-span-4">
            @livewire('categories-selector', ['categoryName' => 'Intereses', 'selectedCategories' => Auth::user()->categories->pluck('id')->toArray()])
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>
        <x-button wire:loading.attr="disabled" wire:target="photo">
            {{ __('Save') }}
        </x-button>
    </x-slot>

</x-form-section>
