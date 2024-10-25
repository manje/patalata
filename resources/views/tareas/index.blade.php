<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Tareas</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-6">Lista de Tareas</h1>
        <div class="bg-white p-6 rounded-lg shadow-md mb-4">
            <p>En esta página los distintos usuarios de la plataforma pueden priorizar las distintas tareas de desarrollo de la plataforma.</p>
            <p>Las distintas tareas corresponden con partes de la plataforma que están descritas al final de esta página.</p>
            <p>El proceso de diseño y construcción de esta plataforma parte de la colaboración con distintos activistas y miembros de movimientos sociales, está votación no tiene garantías ni compromisos de implementación pero si nos sirve para priorizar las tareas de desarrollo y que estén disponible antes las funcionalidades que más interés hay por probar.</p>
            <p>Si has llegado hasta aquí, y te interesa este proyecto, ya sea para dar tu opinión e ideas como posbile futura usuaria, o quieres participar activamente, puedes ponerte en contacto con nostros a través de Telegram: <a href="https://t.me/manjenet">@manjenet</a> (vamos a crear una comunidad de telegram, mientras no lo hacemos dejamos aqui el contacto de uno de los impulsores. </p>
        </div>


            <!-- resources/views/tareas/index.blade.php -->
            @foreach ($tareas as $tarea)
                <div class="bg-white p-6 rounded-lg shadow-md mb-4">
                    <h2 class="text-xl font-semibold">{{ $tarea->nombre }}</h2>
                    <p>Votos: {{ $tarea->usuarios_que_votaron_count }}</p>

                    @if ($tarea->dependencia)
                        <p class="text-sm text-gray-600">Depende de: {{ $tarea->dependencia->nombre }}</p>
                    @endif

                    <div class="mt-4">
                        @if ($tarea->usuariosQueVotaron->contains(auth()->user()))
                            <form action="{{ route('tareas.quitarVoto', $tarea) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Quitar Voto</button>
                            </form>
                        @else
                            <form action="{{ route('tareas.votar', $tarea) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Votar +1</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach

        <div class="bg-white p-6 rounded-lg shadow-md mb-4"
        style="
        white-space: pre-wrap; /* Respeta los saltos de línea */
        text-align: justify; /* Justifica el texto */
        width: 80%; /* Define el ancho del texto, ajusta según necesidad */
        margin: auto; /* Centra el contenedor */
        ">
            * Registo de usuarias

Activistas que así lo deseen, además de registrarse en la plataforma con email, contrasseña y localidad, podrán crearse un perfil de activista que no será público.

Podrán darse de alta en una red de activistas, donde pueden elegir sus intereses, su disponibilidad para colaborar con otros movimientos y sus conocimientos/habilidades para la construcción de un banco de talentos.

Los intereses son una serie de categorías en la que se clasificarán todos los contenidos (feminismo, ecologismo, barrios, economía, cultura, pacifismo, etc. - categorias)

En la disponibilidad una persona puede elegir que tipo de solicitudes de colaboración está dispuesto a recibir (influencers que puedan ayudar a difundir determinadas reivindicaciones, artistas dispuestos a participar en eventos, diseñadores gráficos, mediadores en conflictos, realizar traslados en vehículos, colaborar en la organización de un evento, etc.)

En el banco de talentos se podrán inscribir aquellas personas que sean expertos o tengan conocimientos en alguna disciplina que pueda ser demandada por los movimientos sociales (informáticos, abogados, técnicos medioambientales, educadores sociales, etc.)

Los movimientos sociales podrán usar esta red para realizar peticiones de ayuda, implementando las medidas necesarias para evitar el abuso de esta herramienta como la saturación en el envío de mensajes, de manera que los usuarios puedan limitar tanto el número de mensajes que le lleguen, como la temática de estos.

Dudas:
    La diferencia entre talento y disponibilidad puede ser confusa. En determinados casos está claro la diferencia entre una disponibilidad, como ser voluntario para preparar un evento, y un talento, como un técnico de medioambiente que se le puede pedir contenidos para una campaña. Pero en otros casos, como un diseñador gráfico, ¿es un talento o una disponibilidad?
        Pros para unificarlo: Sencillez
        Pros para separarlo: La parte de disponibilidad está más enfocada a activistas y la parte de talentos a personas menos involucrados en los movimientos sociales pero que quieren poner disposición de estos sus conocimientos profesionales.

* Equipos

Las personas registradas en la plataforma pueden crear equipos, e invitar a otras personas al equipo, de esta manera podrán publicar contenidos a nombre de este equipo.

Los equipos opcionalmente podrán crear un perfil público, aportando información sobre colectivo, sus redes sociales, su web, etc. creando ente todos un directorio de los movimientos sociales de la localidad.

El sistema de roles inicial será Administrador y editor.

¿Incluir el rol de miembro? 
    Pros:
        Las peticiones de ayuda (banco de talentos) a miembros del propio equipo serían inmediatas.
        Posibilitaría crear herramientas de colaboracíón interna como una agenda privada.
    Contras:
        En principio si no va a publicar contenido no tiene sentido que sea miembro del equipo.
        Puede provocar la competición por la "colección" de miembros, como ocurre con los seguidores en redes sociales.
        Implementar esto, sin que existan herramientas de colaboración interna, es inútil y confuso.

* Agenda

En la agenda cualquier equipo o persona podrá publicar cualquier actividad, charlas, presentaciones de libros, manifestaciones, concentraciones, etc., cada evento estará vinculado a una ciudad y puede estar vinculado a una o más categorías.

* Podcasts

La plataforma contará con un espacio donde poder subir podcasts. Cualquier usuario o equipo puede crear un Podcast y añadir episodios. Los podcast estarán vinculados a una localidad y pueden estar vinculdas a varias categorias, tanto el podcast, como cada episodio.

* Denuncia

Un apartado permitirá a cualquier ciudadano realizar una denuncia púbica, ya sea solo con un texto, o adjuntando material multimedia.

* Campañas

Los administradores de los equipos podrán crear campañas dentro de la plataforma, estas campañas pueden estar vinculadas a uno o más equipos. Los editores de estos equipos podrán crear contenidos (eventos, denuncias, podcast, etc.) y vincularlo a una campaña, es decir, los editores de los equipos de la campaña, son también editores de la campaña.

Cualquier activista al crear cualquier contenido podrá vincularlo a una campaña, aunque no pertenezca a ninguno de los equipos que crearon y gestionan la campaña, en este caso los editores de la campaña recibirán un aviso y podrán aceptar o rechazar la vinculación de este contenido con la campaña.

* Comentarios

Todos los contenidos de la plataforma tendrá la opción de recoger comentarios de los usuarios de la plataforma.

¿Se puede publicar un comentario a nombre de un equipo?
    Pros:
        Permite a un colectivo responder a un comentario para aclarar una información.
    Contra:
        Usuarios pueden usar la identidad de un equipo para publicar comentarios con opiniones personales.

* Moderación

Los contenidos publicados en patalata soportarán un sistema de moderación colectiva, que estará destinado a destacar los contenidos más importantes (cuando exista mucha cantidad de contenidos) y ocultar contenidos claramenta dañitos para la plataforma.

Los usuarios tendrán un número de puntos limitados, todos los usuarios los mismos puntos, para durante un periodo de tiempo destacar los contenidos que consideren más importantes. El derecho a realizar funciones de moderación en la plataforma estará supeditado a ser usuarios activos de la plataforma y realizar un buen uso de la platforma en general y de las herramientas de moderación en particular.

Los distintos grupos en cada localidad observarán que se está realizando un buen uso del sistema de moderación, y se intervendrá en el caso que el sistema de moderación sea usado de forma no legítima de manera que se estén tratando de ocultar contenidos legítimos por rivalidades varias.



        </div>


    </div>
</body>
</html>
