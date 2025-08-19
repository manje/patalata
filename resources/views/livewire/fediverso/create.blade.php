<div class="p-4 bg-gray-100 rounded-lg shadow-md" x-data="{ 
            text: @entangle('text'),
            maxLength: {{ $maxLength }} ,
            sensitive: @entangle('sensitive'),
            photos: @entangle('photos'),
            altText: @entangle('altText'),
            removePhoto(index) {
                this.photos.splice(index, 1);
                this.altText.splice(index, 1);
            },
            addPhoto(file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.photos.push({ src: e.target.result, name: file.name });
                    this.altText.push(''); // Inicializar el alt vacío para cada imagen
                    console.log(this.photos);
                };
                reader.readAsDataURL(file);
            },
            resetForm() {
                this.text = '';
                this.photos = [];
                this.altText = [];
                this.sensitive = false;
            } 
            
        }"
        x-on:formReset="console.log('recibiod B')"
        x-init="console.log('init alpine')"
>






    <form 
        wire:submit.prevent="submit" 
        
        >
        <!-- Caja de texto para el contenido -->

        @if ($inreplyto)
            <div class="bg-white p-4 mb-4 text-black rounded-lg shadow-md">
                <div class="flex">
                    <div class='w-full'>
                        Respondiendo a:
                    </div>
                    <div class="cursor-pointer" wire:click="cleaninreplyto()">
                    &times;
                    </div>
                </div>
                <livewire:fediverso.activity :activity="$inreplyto" :diferido="true" :key="$inreplyto"  />
            </div>
        @endif

        <textarea x-model="text" wire:model="text" class="w-full border-gray-300 rounded-md p-2 mb-4" placeholder="Escribe algo..." maxlength="250"></textarea>

        <div class='flex w-full items-center space-x-2 mb-4'>

            <i class="text-xl cursor-pointer fa-solid fa-triangle-exclamation"
                :class="sensitive ? 'text-red-500' : 'text-gray-500'"
                @click="sensitive = !sensitive">

            </i>
            <!-- upload -->
            <i class="text-xl cursor-pointer fa-solid fa-upload text-gray-500" 
            x-on:click.prevent="$refs.fileInput.click()"
            ></i>
            <span class="text-sm text-gray-500 " x-text="maxLength - text.length"></span>
            <x-button class="mt-2">Publicar</x-button>
        </div>

        <div class="flex items-center mb-4" x-show="sensitive">
            <x-input id="summary" type="text" class="mt-1 block w-full" wire:model="summary" autocomplete="summary" placeholder="Advertencia de contenido" />
        </div>


        <div class="col-span-6 sm:col-span-4">
            <input type="file" 
                wire:model="media" 
                x-ref="fileInput" 
                x-on:change="
                        Array.from($refs.fileInput.files).forEach(file => {
                            addPhoto(file);
                        });
                        $refs.fileInput.value = '';  <!-- Reset the input to allow re-upload of the same file -->
                    "
                class="hidden" 
                multiple accept="image/*,audio/*,video/*" />
            <div class="mt-2">
                <template x-for="(photo, index) in photos" :key="index">
                    <div class="flex items-center justify-between mb-3">
                        <template x-if="photo.src.startsWith('data:image/')">
                            <div class="w-20 h-20 bg-cover bg-no-repeat bg-center"
                                x-bind:style="'background-image: url(\'' + photo.src + '\');'">
                            </div>
                        </template>
                        <template x-if="photo.src.startsWith('data:video/')">
                            <video class="w-20 h-20" controls>
                                <source :src="photo.src" x-bind:type="photo.type">
                                Tu navegador no soporta el video.
                            </video>
                        </template>
                        <template x-if="photo.src.startsWith('data:audio/')">
                            <audio class="w-20 h-20" controls>
                                <source :src="photo.src" x-bind:type="photo.type">
                                Tu navegador no soporta el audio.
                            </audio>
                        </template>
                        <textarea x-model="altText[index]" 
                            wire:model.lazy="altText[index]"
                            class="h-20 border p-1 w-full mx-2" 
                            placeholder="Describa la imagen para las personas con dificultads visuales"></textarea>
                        <button type="button" 
                                class="text-red-500 font-bold text-xs"
                                x-on:click="removePhoto(index)">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </template>
            </div>
            <x-input-error for="summary" class="mt-2" />
            <x-input-error for="text" class="mt-2" />
            <x-input-error for="sensitive" class="mt-2" />
        </div>
    </form>


</div>


@script
    <script>
        // Este código es JavaScript que escucha el evento desde Livewire y lo manda a alpine
        $wire.on('formSubmitted', () => {
            console.log('recibiod A');
            // Suponiendo que Alpine.js ya ha sido cargado en la página
            document.querySelector('[x-data]').dispatchEvent(new CustomEvent('formReset'));
        });
    </script>
    @endscript

