<div class="p-6 lg:p-8 bg-white border-b border-gray-200">

    <h1 class=" text-2xl font-medium text-gray-900">
        ¡Bienvenidas a la nueva patalata!
    </h1>

    <p class="mt-6 text-gray-500 leading-relaxed">
        Esta es una maqueta en construcción de lo que será la nueva Patalata. Un espacio para el tejido social de un territorio donde encontrarás una agenda, podcast, denuncias y que contará con herramientas para la movilización social.
        <br>
        Está construido sobre sofware libre, por lo que cualquiera puede crear su propio nodo de esta red, e implementa el protocolo ActivityPub de manera que los usuarios de distintas instancias pueden interactuar entre ellos e intercambiar contenido de manera que una herramientas como la agenda local incluirá eventos que se hayan creado e otros instancias o de cualquier usuario del fediverso.
        <br>
        Cada instacia debe ser moderada por cada colectivo que lo gestione, y gestionar la moderación junto con la sostenibilidad del nodo serán las principales retos una vez se ponga en marcha esta red. 
    </p>
</div>

<div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">

    <div>
        <div class="flex items-center">
            <i class="fa-regular fa-user"></i>
            <h2 class="ms-3 text-xl font-semibold text-gray-900">
                <a href="https://tailwindcss.com/">Comunidad</a>
            </h2>
        </div>
        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
            Un banco de talento y recursos de el que podrán hacer uso los distintos colectivos de la ciudad. Podrás inscribirte y ofrecer tus conocimientos, habilidades y capacidades.
            <br>
            Esta herramienta no estará federada, y los moderadores de la instancia deberán moderar las peticiones de ayuda para evitar abusos y saturación para lo que habrá que experimientar distintas técnicas.
            <br>
            Este módulo inicialmente no será federado por la complejidad de establecer mecanismos federados de moderación para evitar spam.
            <br>
            Se considera una herramienta importante y se comenzará a desarrolar una vez se termine el desarrollo de otros módulos como las campañas y las portadas territoriales.
        </p>
    </div>

    <div>
        <div class="flex items-center">
            <i class="fa-solid fa-tree-city"></i>
            <h2 class="ms-3 text-xl font-semibold text-gray-900">
                Desde cada localidad
            </h2>
        </div>

        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
            Las instancias pueden crear portadas territoriales, que agrupan en un mismo espacio todos los contenidos geolocalizados en un territorio delimitado, distinguiendo distintos contenidos (artículos, eventos, denuncias) y constuyendo una agenda.
            <br>
            Al crear contenidos siempre se pueden geolocalizar, no solo eventos sino también otros contenidos como los artículos o las denuncias (que a nivel protocolo ActivityPub son también artículos). Esto permite que distintas instancias que se solapan en el territorio puedan incluir en las portadas territoriales contenidos de otras instancias.
            <br>
            La vocación es principalmente nodos centrados en una ciudad, pero se contempla que distintas instancias puedan solaparse territorialmente, por distintas razones. Por un lado un municipio puede tener un tamaño que permita instancias más pequeñas para barrios o pedanías, o existen espacios más amplios que una localidad que tienen dinámicas sociales que justifican un espacio común de intercambio.
        </p>
    </div>

    <div>
        <div class="flex items-center">
            <!-- solo icono free -->
            <i class="fa-solid fa-people-group"></i>
            <h2 class="ms-3 text-xl font-semibold text-gray-900">
                <a href="/">Equipos</a>
            </h2>
        </div>
        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
            Las personas pertenecientes a colectivos, grupos de afinidad u otro tipo de proyectos pueden agruparse y publicar contenidos colectivamente.
            <br>
            En principio no se plantea la creación de herramientas de trabajo en grupo, que tal complejidad sería demasiado ambicioso para este proyecto.
            <br>
            La principal funcionalidad de un equipo será el acceso al banco de talentos y recursos, la participación y creación de campañas y la gestión de una identidad colectiva en el fediverso.
        </p>

    </div>

    <div>
        <div class="flex items-center">
            <!-- solo icono free -->
            <i class="fa-solid fa-bullhorn"></i>
            <h2 class="ms-3 text-xl font-semibold text-gray-900">
                <a href="/">Campañas</a>
            </h2>
        </div>

        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
            Los colectivos y movimientos podrán crear campañas dentro de la plataforma, y asociar contenidos (denuncias, eventos, podcasts, etc.) a la campaña.
            <br>
            Las campañas recopilan contenidos (eventos, artículos, etc.) de distintos movimientos de la ciudad. Las campañas tienes propietarios y miembros, los cuales pueden publicar contenido en la campaña, aunque cualquier otro colectivo puede publicar un contenido y asociarlo a una campaña, y serán los propietarios de la campaña los que moderen estos contenidos.
            <br>
            Las campañas se crean en la instancia del usuario o equipo que crea la campaña, pero usuarios y equipos de disintas instancias pueden añadirse como miembros o propietarios y en el caso de los propietarios gestionar la moderación de contenidos de no-miebmros enviados a la campaña así como añadir a nuevos miembros.
        </p>

    </div>



    <div>
        <div class="flex items-center">
            <i class="fa-regular fa-calendar"></i>
            <h2 class="ms-3 text-xl font-semibold text-gray-900">
                <a href="/agenda">Agenda Local</a>
            </h2>
        </div>

        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
            La agenda es un espacio común donde se agrupan todos los eventos asociados a una ciudad. La agenda trambién hará que los usuario registrados vean los eventos creados por cualquier perfil del fediverso a el que siga, y publique un evento.
        </p>

    </div>

    <div>
        <div class="flex items-center">
            <!-- solo icono free -->
            <i class="fa-solid fa-people-roof"></i>
            <h2 class="ms-3 text-xl font-semibold text-gray-900">
                <a href="/">Denuncias</a>
            </h2>
        </div>

        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
            Herramienta para que al ciudadanía y movimientso puedan compartir con la comunidad denuncias públicas sobre cualquier asunto, adjuntando fotos y documentación.
        </p>

    </div>


    <div>
        <div class="flex items-center">
            <!-- solo icono free -->
            <i class="fa-solid fa-podcast"></i>
            <h2 class="ms-3 text-xl font-semibold text-gray-900">
                <a href="/">Podcast</a>
            </h2>
        </div>

        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
            Un espacio para la difusión de los podcast que distintos grupos de la ciudad realizan.
            <br>
            Aunque agrupar podcast por territorio, o por campañas, puede ser una herramienta muy útil, es una de las herramientas menos prioritarias, comparadas con otras más imprescindibles, e incluso no necesaria en el primer lanzamiento.
        </p>
    </div>

    <div>
        <div class="flex items-center">
            <!-- solo icono free -->
            <i class="fa-solid fa-comment-dots"></i>
            <h2 class="ms-3 text-xl font-semibold text-gray-900">
                <a href="/">Construcción colectiva</a>
            </h2>
        </div>
        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
            Iniciamos la construcción de esta herramienta en estrecha colaboración con personas que particpan de distintos movimientos sociales de la provincia de Cádiz.
        </p>
        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
            En nuestro pequeño <a href='/tareas'>gestor de tareas</a> puedes ver el borrador de la estructura de este proyecto y priorizar las areas a desarollar.
            <br>
            <!-- boton para ir a tareas -->
            <a href="/tareas" class=" mt-4 btn btn-sx btn-primary ">Tareas</a>
        </p>
    </div>

</div>
