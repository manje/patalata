
<div class="navbar bg-base-100">
  <div class="flex-1">
    <a href="/" class="btn btn-ghost text-xl">patalata.net</a>

    <ul class="menu menu-horizontal px-1">
      <li><a href="{{ route('eventos.index') }}" :active="request()->routeIs('eventos.index')">Agenda</a></li>
    </ul>
  </div>
  <div class="flex-none">
    <ul class="menu menu-horizontal px-1">
        @auth
            @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                <details class="">
                    <summary>
                    @if (Auth::user()->allTeams()->count() > 0)
                       {{ Auth::user()->currentTeam->name }} 
                    @else
                        Equipos
                    @endif
                    </summary>
                        @if (Auth::user()->allTeams()->count() > 0)
                            <li ><a href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">Configuración Equipo</a></li>
                        @endif
                        @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                            <li><a href="{{ route('teams.create') }}">Create New Team</a></li>
                        @endcan
                        @if (Auth::user()->allTeams()->count() > 1)
                            <!--div class="border-t border-gray-200"></div-->
                            {{--<div class="block px-4 py-2 text-xs text-gray-400">Switch Teams</div>
                            @foreach (Auth::user()->allTeams() as $team)
                                <x-switchable-team :team="$team" />
                            @endforeach --}}
                            <li>Cambiar Equipo</li>
                            @foreach (Auth::user()->allTeams() as $team)
                                <li>
                                    <form method="POST" action="{{ route('current-team.update') }}" x-data>
                                        @method('PUT')
                                        @csrf
                                        <input type="hidden" name="team_id" value="{{ $team->id }}">
                                        <a href="#" @click.prevent="$root.submit();">{{ $team->name }}</a>
                                    </form>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </details>
            @endif
            <li>
            <details>
              <summary>
                <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="rounded-full h-5 w-5 object-cover inline">

                {{ Auth::user()->name }}  </summary>
              <ul class="bg-base-100 rounded-t-none p-2">
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
              </ul>
            </details>
            </li>
        @else
            <li>
                <a href="{{ route('login') }}">Login</a>
            </li>
            <li>
                <a href="{{ route('register') }}">Register</a>
            </li>
        @endauth
    </ul>
  </div>
</div>

 