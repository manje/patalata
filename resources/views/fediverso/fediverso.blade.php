<x-fediverso-layout>
    <div class="border-b pb-4 p-2">
        <p>
        Estamos implementando ActivityPub
        </p> <p>
            Te puden seguir, y puedes seguir a otros usuarios de todo el Fediverso.
        @auth
        </p> <p>
            Tu usuario en el fediverso es {{ $userfediverso->slug }}{{ "@" }}{{ request()->getHost() }} , 
            pueden seguirte en otras instancias.
        @endauth
        </p> <p>
            Las actividades como notas o artículos se envían
            a tus seguidores, pero todavía no está implementado publicar desde el pequeño
            textarea de la izquierda, ni tampoco las interaciones como replys, likes y rts.
        </p> <p>
            Al ser una primera implementación puede tener todavía errores.
        </p> <p>
            Para probar como seguir a cuentas del fediverso, os dejo
            varios enlaces a distintos perfiles:
            <br>
            <br>



            @php
            
            $list=[
                'vamonosjuntas@masto.es',
                'lavillanavk@mastodon.social','lacasainvisible@xarxa.cloud',
                'nolesdescasito@mstdn.social','ElSaltoDiario@mastodon.social','eldiarioes@mastodon.world','ctxt@mastodon.world',
                'infusionideologica@paquita.masto.host','espanabizarra@mastodon.social','PixelRobot@paquita.masto.host',
                'teclista@mas.to','paquita@paquita.masto.host','velvetmolotov@masto.es','euklidiadas@masto.es'
            ];
            foreach ($list as $p) echo "<a href='/@$p'>@$p</a> ";

            @endphp


        </p> 
    </div>
    <div id="timeline-container" class="flex-1 overflow-y-scroll">
        <livewire:fediverso.timeline   />
    </div>

    <script>

let container = window;

window.onscroll = function() {
    loadMore();
};

function loadMore() {
    let scrollTop = window.scrollY || document.documentElement.scrollTop;
    let clientHeight = window.innerHeight;
    let scrollHeight = document.documentElement.scrollHeight;
    if (scrollTop + clientHeight >= scrollHeight) {
        Livewire.dispatch('loadMore');
        console.log('loadMore');
        // enseño el div de buscandomas
        document.getElementById('buscandomas').style.display = 'block';
    }
}
 

    </script>

</x-fediverso-layout>
