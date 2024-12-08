<x-fediverso-layout>
    <div class="border-b pb-4 p-2">
        <p>
        Estamos implementando ActivityPub
        </p> <p>
            Te puden seguir, y puedes seguir a otros usuarios de todo el Fediverso.
        @auth
        </p> <p>
            Tu usuario en el fediverso es {{ Auth::user()->slug }}{{ "@" }}{{ request()->getHost() }} , 
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
            <a href='/@lacasainvisible@xarxa.cloud'>@lacasainvisible@xarxa.cloud</a><br>
            <a href='/@infusionideologica@paquita.masto.host'>@infusionideologica@paquita.masto.host</a><br>
            <a href='/@ElSaltoDiario@mastodon.social'>@ElSaltoDiario@mastodon.social</a><br>
            <a href='/@lavillanavk@mastodon.social'>@lavillanavk@mastodon.social</a><br>
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
    console.log(scrollTop, clientHeight, scrollHeight);
    if (scrollTop + clientHeight >= scrollHeight) {
        console.log("Has llegado al final de la página");
        // Aquí puedes emitir un evento a Livewire
        Livewire.dispatch('loadMore');
    }
}
 

    </script>

</x-fediverso-layout>
