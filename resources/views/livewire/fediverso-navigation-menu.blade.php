<div class="m-4">


    <div class="mb-4 ">
        <input 
            type="text" 
            placeholder="Buscar..." 
            class="input input-bordered w-full"
        />
    </div>
    <ul class="menu bg-base-100 w-full rounded-box text-xl">
    <li>
            <a href="/" class="flex items-center">
                <i class="fa-solid fa-house mr-2"></i>
                {{ config('app.name') }}
            </a>
        </li>
        <li>
            <a href={{ route('fediverso.index') }} class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 256 256"><path fill="currentColor" d="M212 96a27.8 27.8 0 0 0-10.51 2L171 59.94A28 28 0 1 0 120 44a29 29 0 0 0 .15 2.94L73.68 66.3a28 28 0 1 0-28.6 44.83l1.85 46.38a28 28 0 1 0 32.74 41.42L128 212.47a28 28 0 1 0 49.13-18.79l27.21-42.75A28 28 0 1 0 212 96m-140.81 8.36L113.72 129l-41.46 32.22a28 28 0 0 0-9.34-4.35l-1.85-46.38a28 28 0 0 0 10.12-6.13M149.57 72a27.8 27.8 0 0 0 8.94-2L189 108.06a27.9 27.9 0 0 0-4.18 9.22l-46.57 2.22ZM82.09 173.85L124 141.26l15.94 47.83a28.2 28.2 0 0 0-7.6 8L84 183.53a28 28 0 0 0-1.91-9.68M156 184h-.89l-16.18-48.53l46.65-2.22a27.9 27.9 0 0 0 5.28 9L163.65 185a28 28 0 0 0-7.65-1M126.32 61.7a28.4 28.4 0 0 0 7.68 6.54l-11.3 47.45l-43.47-25.17A28 28 0 0 0 80 84a29 29 0 0 0-.15-2.94Z"/></svg>
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
            <a href="/{{'@'}}{{ $identidad->slug }}" class="flex items-center">
                @if (isset($identidad->GetActivity()['icon']))
                <img src="{{ $identidad->GetActivity()['icon']['url'] }}" class="w-6 h-6 rounded-full   ">
                @else
                <i class="fa-solid fa-user mr-2"></i>
                @endif
                {{'@'}}{{ $identidad->slug }}
            </a>
        </li>
    </ul>
    <div class="mt-4">
        <livewire:fediverso.create />
    </div>
    @if (Auth::user()->allTeams()->count() > 0)

        <div class="mt-4" x-data='{showlist: false}'>
            <button class="flex items-center btn w-full" x-on:click="showlist = !showlist">
            <i class="fa fa-refresh" aria-hidden="true"></i>
                Cambiar de cuenta
            </button>
            <div x-show="showlist">
                <a href='{{ route('fediverso.index') }}/?user=0'>
                    <div class='flex flex-items border py-2 mt-2 cursor-pointer'>
                            <div class='mx-4'>
                                @if (Auth::user()->profile_photo_url)
                                    <img src="{{ Auth::user()->profile_photo_url }}" class="w-6 h-6 rounded-full">
                                @else
                                    <i class="fa-solid fa-user mr-2"></i>
                                @endif
                            </div>
                            {{ Auth::user()->name }}
                    </div>
                </a>
                @foreach (Auth::user()->allTeams() as $team)
                    <a href='{{ route('fediverso.index') }}/?user={{ $team->id }}'>
                        <div class='flex flex-items border py-2 mt-2 cursor-pointer'>
                            <div class='mx-4'>
                                @if ($team->getProfilePhotoUrlAttribute())
                                    <img src="{{ $team->getProfilePhotoUrlAttribute() }}" class="w-6 h-6 rounded-full">
                                @else
                                    <i class="fa-solid fa-user mr-2"></i>
                                @endif
                            </div>
                            {{ $team->name }}
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif


</div>
