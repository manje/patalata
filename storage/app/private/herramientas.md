# Una red activista descentralizada

Este documento describe un software que se está desarrollando como
herramienta para los movimientos sociales.

Las distintas instancias de este software se federan entre ellas mediante el
protocolo ActivityPub.

Mientras que algunas de estas herramientas están ya diseñadas y en proceso
de implementanción otras están todavía en fase de estudio, pero este documento
está totalmente abierto.

# Registo de usuarixs

Cualquier persona puede registrarse indicando a que localidad pertenece, y
eligiendo un nombre de usuarix. De esta manera al acceder
a la herramienta verá los contenidos asociados a su localidad.

Adicionalmente podrá darse de alta en una red de activistas, donde pueden elegir sus intereses, su disponibilidad
para colaborar con otros movimientos y sus talentos para la construcción de un banco de talentos.

Los intereses son una serie de categorías que hay que definir, y en las que se clasificarán todos los 
contenidos (feminismo, ecologismo, barrios, economía, cultura, pacifismo, etc. - categorias)

En la disponibilidad el usuarix podrá elegir que tipo de solicitudes de colaboración está dispuesto a recibir (participar de 
crowfundings, realizar traslados en vehículos, colaborar en la organización de un evento, etc.)

En el banco de talentos se podrán inscribir aquellos usuarixs que sean expertos o tengan conocimientos en
alguna disciplina que pueda ser demandada por los movimientos sociales (informáticos, abogados, técnicos
medioambientales, educadores sociales, etc.)

Los movimientos sociales podrán usar esta red para lanzar peticiones de ayuda,
se implementarán las medidas necesarias
para prevenir el abuso como la saturación en el envío de mensajes, de manera que los 
usuarixs puedan limitar tanto el número de mensajes que le lleguen, como la temática de estos.

# Espacio colectivo

Los usuarixs pueden crear equipos, de estea manera podrán publicar contenidos de manera conjunta.

Los equipos tendrán un perfil público, donde se pueda ver la información del colectivo, sus redes sociales, 
su web, etc., está información servirá para crear un directorio de los movimientos sociales
de cada localidad.

El sistema de roles inicial será Administrador y editor, además de que cualquier usuarix del fediverso podrá seguir a un equipo.

Los grupos estarán federados con el modelo Group de ActivityPub.

# Agenda

En la agenda cualquier equipo o persona podrá publicar cualquier actividad, charlas, presentaciones de libros, 
manifestaciones, concentraciones, etc., cada evento pertenecerá a una ciudad y puede estar 
vinculado a una o más categorías.

Los eventos estarán federados con el model Event de ActivityPub, para
modelarlo usaremos de referencia los eventos generados por el software
Mobilizion. ( https://docs.joinmobilizon.org/contribute/activity_pub/ )

En la página de cada localidad se verán los eventos de esa localidad, además
en agenda, el usuarix ve los eventos de su localidad
junto con los eventos de perfiles que sigue, que pueden ser usuarixs, equipos o campañas
de cualquier de perfil del fediverso.

# Campañas

Los equipos podrán crear campañas dentro de la plataforma, estas campañas
podrán pertecener a uno o más equipos.

Todos los contenidos creados en la plataforma se podrán vincular a una
campaña, ya sean contenidos creados por personas o por equipos (eventos,
artículos, denuncias, etc.).

Cuando una persona o equipo crea un contenido para una campaña, pero el
usuarix no es editor de uno de los equipos de la campaña, podrá pedir
la vinculación de ese contenido a la campaña, y un editor, o administrador,
de cualquiera de los equipos miembros de esta campaña podrá aceptarlo o
rechazarlo.

Este sistema generará una página para cada campaña, con las actividades
y contenidos vinculados a esta campaña.

Las Campañas se federan a fediverso como un Group, permitiendo a cualquier
usuarix del fediverso recibir todos estos contenidos.

Cuando un contenido se vincula a una campaña lo que se producirá en
ActivityPub es un impulso (Announce), por lo que llegará el contenido a
todos los seguidores de la campaña.

# Podcasts

Para el sistema para podcast se usarán los formatos usados por Castopood
(pendiente de estudio)

# Denuncia

Un apartado permitirá a cualquier ciudadano (a través de un usuarix o un
equipo) realizar una denuncia púbica, ya sea solo con un texto, o adjuntando material multimedia.

Cada denuncia se publicará en el fediverso como un artícuo (Article).

# Comentarios

Todos los contenidos de la plataforma tendrá la opción de recoger comentarios de los usuarixs de la plataforma.

Los comentarios se implementarán a traves de notas (toots en Mastodon, Note
en ActivityPub)

# Fediverso

El fediverso es una alternativa distribuida a la redes sociales
centralizadas, donde cualquier persona puede crear instancias y las
instancias se comunican entre ella, si es la primera vez que escuchas hablar
del Fediverso te recomendamos la [ayuda de la campaña #VamosnosJuntas](https://vamonosjuntas.org/help)

Todos los activistas, equipos y las campañas, tendrán un usuarix en el fediverso, podrán ser seguidos
desde cualquier instancia 
como Mastodón, pixelfed o PeerTube. De esta manera los contenidos, artículos, denuncias, podcasts, eventos,
etc. se distribuirán a través del fediverso. Estos contenidos por lo tanto podrán
ser comentados tanto por usuarixs registrados en la plataforma como por cualquier usuarix del fediverso.

Los usuarixs y equipos podrán desde esta plataforma participar del fediverso, siguiendo otras cuentas
de la propia plataforma o de otras instancias del fediverso, contando con un timeline sin algoritmos.

Los contenidos generados por este software usan la propiedad Place de
ActivityPub para localizar geográficamente los contenidos.

Cuando la instancia reciba contenidos de otra instancia, comprobará el valor
Place para comprobar a través de las coordenadas gps
si pertenece a alguna localidad configurada en la instancia y la añade a la
página de la localidad.

# Fronted de localidades

Cada instancia puede crear una página pública para las localidades a las que va destinada
la instancia, en este portal se muestran los
contenidos de esa localidad, Eventos (Agenda), Artículos, Denuncias, Podcasts,
etc., tanto de esta instancias como de otras.

Los contenidos de usuarixs o grupos de otras instancias llegan a nuestra instancia si algún
usuarix de nuestra instrancia le sigue, y aparecerá vinculado a una
localidad si el contenido está geolocalizado, lo que será habitual en
eventos pero no en artículos de instancias que usan otro software ya que
normalmente los ususarios no geolocalizan sus posts.

Aunque hablamos de localidades en realidad se podrán crear fronted de cualquier ámbito
territorial.

# Gobernanza y Moderación

La gobernanza de las instancias del fediverso es una de los principales
retos tecnopolíticos actualmente.

A un usuarix de una instancia no se le puede pedir la participación en toda la
gestión (técnica, administrativa, moderación, etc.) por lo que inevitablemente existirán 
distintos grados de implicación.

De los participantes en una instancia destinada a activistas y movimientos
sociales esperamos un mayor grado de implicación, por lo que 
es un espacio en principio faborable para poner en marcha herramientas participativas de
moderación.

Es indispensable contar en último lugar con un espacio asambleario que
supervise, analize y reflexione sobre las posibles dinámicas tóxicas 
o métodos de abuso. Espacios más humanos y directos a los que acudir si una
persona siente que está siendo perjudicada de alguna manera y los métodos de moderación implementados
no están funcionando correctamente
o no solucionan ese problema tal como están implementados.

También es importanta realizar una reflexión colectiva de análisis y evaluzación
de las dinámicas comunicativas que se generan, idealmente de manera
presencial.

La moderación consiste princialmente en impedir que
determinados contenidos no apropiados se puedan ver, esta decisión puede ser
temporal o permanente, puede ser sobre un cotenido, sobre un usuarix, o
sobre una instancia, y puede ser un bloqueo total, o solo silenciar.

Todo proceso de moderación se inicia con la denuncia de un contenido
concreto. Cualquier usuarix puede denunciar un contenido independientemente
de si se trata de un contenido de nuestra instancia o importada de otra
instancia. A quien denuncia un contenido se le permitirá bloquear al usuarix
que ha generado ese contenido, de esta manera no volverá a ver contenidos de
ese usuarix.

Una vez se incorpora la denuncia a una base de datos de denuncias esta
denuncia deb ser rehazada o aceptada, y en el caso de ser aceptada
determinar las consescuencias, principalmente bloqueo o silenciamiento del
contenido, el usuarix, o la instancia remota.

Se deben establecer distintos grados de gravedad, de manera que repetidos
bloqueos de contenidos provoquen un bloqueo del usuarix.

El sistema debe contar con la posibilidad de que cualquier usuarix pueda
incribirse como moderador y participar de estas decisiones. Estos usuarixs 
tienen que valorar los incidentes que se le asginen, es decir, no pueden
acceder directamente para participar en una denuncua elegida por él mismo, y
además deben mantener cierto compromiso de participación (X evaluaciones a
la semana).

Todas las incidencias deben ser evaluadas por más de un usuarix, y
al menos uno de ellos debe tener una trayectoria de
evaluaciones certeras.

Se considera que un moderador tiene una trayectoria de evaluaciones certera
si normalmente coinciden sus evalucación con las evaluaciones de otros
usuarixs a los mismos contenidos.

Este sistema sin supervisión podría provocar vicios y que provocara que un grupo
con una visión determinada tomara el control del sistema de moderación
permitiendo contenidos ilícitos o censurando contenidos lícitos,
provocando que los que moderan corectamente sean minoría y por lo tanto el
sistema considere que no tiene una trayectoria de evaluaciones certeras.

Para evitar esta situación, se puede crear una segunda capa de moderación donde se revisen
las evaluaciones que no hayan sido unánimes.

En cualquier caso serán necesarios espacios asamblearios de supervisión más humanos y no
automatizados que supervisen los conflictos e intervengan si fuese
necesario, así como que puedan adaptar parámetros de la configuración del
sistema de moderación, como cuantos usuarixs deben evaluar cada denuncia,
y a partir de que proporción de moderaciones "correctas"
se considera que un moderador tiene una trayectoria de
evaluaciones certeras.

Sería interesante que las evaluaciones se resolvieran por concenso, y disponer de algún tipo
de mecanismo que se pusiera en marcha si alguna denuncia no se resuelve por una amplia
mayoría elevándolo a otro mecanismo de resolución.

Esta sección está muy desarrollada, aunque no hay nada implementado, así que se trata
más de una reflexión o punto de partida.

https://www.colorado.edu/lab/medlab/2024/12/18/how-build-governable-spaces-online-communities
https://www.contributor-covenant.org/


