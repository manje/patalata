<div>

<div class="navbar bg-base-100">
  <div class="flex-1">
    <a href="{{ config('app.url') }}" class="btn btn-ghost text-xl">{{ config('app.name') }}</a>

    <ul class="menu menu-horizontal px-1">
      <li><a href="{{ route('eventos.index') }}" >Agenda</a></li>
    </ul>
    <ul class="menu menu-horizontal px-1">
      <li><a href="{{ route('posts.index') }}" >Artículos</a></li>
    </ul>
    <ul class="menu menu-horizontal px-1">
      <li><a href="{{ route('denuncias.index') }}" >Denuncias</a></li>
    </ul>
    <ul class="menu menu-horizontal px-1">
      <li><a href="{{ route('notas.index') }}" >Notas</a></li>
    </ul>
  </div>
  <div class="flex-none">        
    @auth
    <ul class="menu menu-horizontal px-1">
      <li>
        <details>
          <summary>Equipos</summary>
          <ul class="bg-base-100 rounded-t-none p-2">
            @if (Auth::user()->allTeams()->count() > 0)
                <li><a href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">{{ Auth::user()->currentTeam->name }}</a></li>
                <li><a href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">Configuración</a></li>
                @if (Auth::user()->allTeams()->count() > 1)
                    <li>Cambiar Equipo</li>
                    @foreach (Auth::user()->allTeams() as $team)
                        <li>
                            <form method="POST" action="{{ route('current-team.update') }}" x-data>
                                @method('PUT')
                                @csrf
                                <input type="hidden" name="team_id" value="{{ $team->id }}">

                                    @if (Auth::user()->isCurrentTeam($team))
                                        <i class="fas fa-check"></i>
                                    
                                    
                                    @endif

                                <a href="#" @click.prevent="$root.submit();">{{ $team->name }}</a>
                            </form>
                        </li>
                    @endforeach
                @endif
            @endif
                <li><a href="{{ route('teams.create') }}">Crear Equipo</a></li>
          </ul>
        </details>
      </li>
    </ul>


    <div class="dropdown dropdown-end">
      <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
        <div class="w-10 rounded-full">
          <img
            src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}"
          />
        </div>
      </div>
      <ul
        tabindex="0"
        class="menu menu-sm dropdown-content bg-base-100 rounded-box z-[1] mt-3 w-52 p-2 shadow">
        <li>{{ Auth::user()->name }}</li>
              <li><a href="{{ route('profile.show') }}">Perfil</a></li>
                  @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                      <li><a href="{{ route('api-tokens.index') }}">API Tokens</a></li>
                  @endif
              <li>
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <a href="https://1.patalata.net/logout" @click.prevent="$root.submit();">{{ __('Log Out') }}</a>
                </form>
              </li>
        <li>
      </ul>
    </div>
    @else
        <ul class="menu menu-horizontal px-1">
            <li>
                <a href="{{ route('login') }}">Login</a>
            </li>
            <li>
                <a href="{{ route('register') }}">Register</a>
            </li>
        </ul>
    @endauth



  </div>
</div>

</div>
