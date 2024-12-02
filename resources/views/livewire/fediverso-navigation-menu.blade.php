<div class="m-4">


    <div class="mb-4 ">
        <input 
            type="text" 
            placeholder="Buscar..." 
            class="input input-bordered w-full"
        />
    </div>
    <ul class="menu bg-base-100 w-full rounded-box">
    <li>
            <a href="/" class="flex items-center">
                <i class="fa-solid fa-house mr-2"></i>
                {{ config('app.name') }}
            </a>
        </li>
        <li>
            <a href={{ route('fediverso.index') }} class="flex items-center">
                <i class="fa-solid fa-comments mr-2"></i>
                Fediverso
            </a>
        </li>
        <li>
            <a href="#" class="flex items-center">
                <i class="fa-solid fa-bell mr-2"></i>
                Notificaciones
            </a>
        </li>
        <li>
            <a href="#" class="flex items-center">
                <i class="fa-solid fa-user mr-2"></i>
                Perfil
            </a>
        </li>
    </ul>
    <div class="mt-4">
        <textarea 
            class="textarea textarea-bordered w-full" 
            placeholder="¿Qué estás pensando?"></textarea>
        <button class="btn btn-primary mt-2 w-full">
            Publicar
        </button>
    </div>



</div>
