<x-fediverso-layout>


                <!-- Columna Derecha -->
                <div class="col-span-9">
                    <div class="p-4 bg-white shadow rounded-lg space-y-4">


                        <div class="border-b pb-4">
                            <p>
                            En esta primera página el usuario debe poder elegri ver las actividades
                            de las personas que sigue, de su ciudad, de su provincia, de su comunidad
                            o de toda la página.
                            </p> <p>
                                De momento solo funciona seguir y que te sigan.
                            @auth
                            </p> <p>
                                Tu usuario en el fediverso es {{ Auth::user()->slug }}{{ "@" }}{{ request()->getHost() }} , 
                                pueden seguirte en otras instancias.
                            @endauth
                            </p> <p>
                                Funciona seguir y que te sigan, las actividades como notas o artículos se envían
                                a tus seguidores, pero todavía no está implementado el recibir las actividades.
                            </p> <p>
                                Al ser una primera implementación puede tener todavía errores.
                            </p> <p>
                                Para probar como seguir a cuentas del fediverso, os dejo
                                varios enlaces a distintos perfiles.
                                <br>
                                <br>

                                <a href='https://1.patalata.net/@lavillanavk@mastodon.social'>@lavillanavk@mastodon.social<br>
                                <a href='https://1.patalata.net/@lacasainvisible@xarxa.cloud'>@lacasainvisible@xarxa.cloud<br>
                                <a href='https://1.patalata.net/@infusionideologica@paquita.masto.host'>@infusionideologica@paquita.masto.host<br>
                                <a href='https://1.patalata.net/@ElSaltoDiario@mastodon.social'>@ElSaltoDiario@mastodon.social<br>
                            </p> 
                        </div>


                    @foreach (range(1, 5) as $index)
                        <div class="border-b pb-4">
                            <div class="flex items-start space-x-4">
                                <div class="avatar">
                                    <div class="w-12 rounded-full">
                                        <img src="https://via.placeholder.com/150" alt="Avatar">
                                    </div>
                                </div>
                                <div>
                                    <h2 class="font-bold text-lg">Usuario {{ $index }}</h2>
                                    <p class="text-sm text-gray-500">@usuario{{ $index }} - Hace {{ $index }} h</p>
                                    <p class="mt-2">
                                        Este es un ejemplo de contenido del tuit número {{ $index }}. 
                                        ¡DaisyUI hace que todo luzca genial!
                                    </p>
                                </div>
                            </div>
                            <div class="mt-2 flex space-x-4 text-gray-500">
                                <button class="flex items-center space-x-1">
                                    <i class="fa-regular fa-heart"></i>
                                    <span>Me gusta</span>
                                </button>
                                <button class="flex items-center space-x-1">
                                    <i class="fa-solid fa-retweet"></i>
                                    <span>Retwittear</span>
                                </button>
                                <button class="flex items-center space-x-1">
                                    <i class="fa-solid fa-comment"></i>
                                    <span>Comentar</span>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
</x-fediverso-layout>
