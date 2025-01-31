<x-form-section submit="updateMunAndInt">
    <x-slot name="title">
        Perfil en {{ config('app.name') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Modifique su localidad y/o seleccione sus intereses.') }}
    </x-slot>
    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
    

            <div class="my-4">
                <x-label for="place_id" value="{{ __('Localidad') }}" />
                <!-- Desplegable de localidades -->
                <select id="place_id" name="place_id" 
                    wire:model="place_id"
                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="" >{{ __('Seleccione una localidad') }}</option>
                    @foreach($places as $place)
                        <option value="{{ $place->id }}" {{ old('place_id') == $place->id ? 'selected' : '' }}>
                            {{ $place->name }}
                        </option>
                    @endforeach
                </select>
            </div>

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
