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
            <p>Si has llegado hasta aquí, y te interesa este proyecto, ya sea para dar tu opinión e ideas como posbile futura usuaria, o quieres participar activamente, puedes ponerte en contacto con nostros a través de Telegram: <a href="https://t.me/manjenet">@manjenet</a> (vamos a crear una comunidad de telegram, mientras no lo hacemos dejamos aqui el contacto de uno de los impulsores.)</p>
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

        <div class="bg-white p-6 rounded-lg shadow-md mb-4">
            {!! $texto !!}
        </div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.1.0/github-markdown-light.min.css">

<style>
h1 {
  font-size: 18pt;
  font-weight: bold;
  margin-top: 20px;margin-bottom: 10px;
}
h2 {
  font-size: 14pt;
  font-weight: bold;
  margin-top: 10px;margin-bottom: 5px;
}
h3 {
  font-size: 12pt;
  font-weight: bold;

}
</style>


    </div>
</body>
</html>
