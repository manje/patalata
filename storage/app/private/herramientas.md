
# Una red activista descentralizada

Este documento describe un software que se está desarrollando como
herramienta para los movimientos sociales.

Las distintas instancias de este software se federan entre ellas mediante el
protocolo ActivityPub.

Mientras que algunas de estas herramientas están ya diseñadas y en proceso
de implementanción otras están todavía en fase de estudio, por lo que puede
ser modificado, de hecho animamos a enviarnos cualquier tipo de comentario.

# Registo de usuarios

Cualquier persona puede registrarse indicando a que localidad pertenece, y
eligiendo un nombre de usuario. De esta manera al acceder
a la herramienta verá los contenidos asociados a su localidad.

Adicionalmente podrá darse de alta en una red de activistas, donde pueden elegir sus intereses, su disponibilidad
para colaborar con otros movimientos y sus talentos para la construcción de un banco de talentos.

Los intereses son una serie de categorías que hay que definir, y en las que se clasificarán todos los 
contenidos (feminismo, ecologismo, barrios, economía, cultura, pacifismo, etc. - categorias)

En la disponibilidad el usuario podrá elegir que tipo de solicitudes de colaboración está dispuesto a recibir (participar de 
crowfundings, realizar traslados en vehículos, colaborar en la organización de un evento, etc.)

En el banco de talentos se podrán inscribir aquellos usuarios que sean expertos o tengan conocimientos en
alguna disciplina que pueda ser demandada por los movimientos sociales (informáticos, abogados, técnicos
medioambientales, educadores sociales, etc.)

Los movimientos sociales podrán usar esta red para lanzar peticiones de ayuda,
se implementarán las medidas necesarias
para prevenir el abuso como la saturación en el envío de mensajes, de manera que los 
usuarios puedan limitar tanto el número de mensajes que le lleguen, como la temática de estos.

# Espacio colectivo

Los usuarios pueden crear equipos, de estea manera podrán publicar contenidos de manera conjunta.

Existe un directorio de colectivos e inciativas en cada localidad, generando
así una guia de recursos.

Los equipos opcionalmente podrán crear un perfil público, donde se pueda ver la información del colectivo, sus redes sociales, 
su web, etc., está información servirá para crear un directorio de los movimientos sociales
la lcoalidad.

El sistema de roles inicial será Administrador y editor, además de que
cualquier usuario del fediverso puede seguir a un equipo.

Los grupos estarán federados con el model Group de ActivityPub.


# Agenda

En la agenda cualquier equipo o persona podrá publicar cualquier actividad, charlas, presentaciones de libros, 
manifestaciones, concentraciones, etc., cada evento pertenecerá a una ciudad y puede estar 
vinculado a una o más categorías.

Los evento estarán federados con el model Event de ActivityPub, para
modelarlo usaremos de referencia los eventos generados por el software
Mobilizion. ( https://docs.joinmobilizon.org/contribute/activity_pub/ )

En la página de cada localidad se verán los eventos de esa localidad, además,
en la sección agenda, el usuario ve los eventos de su localidad
junto con los eventos de perfiles que sige, que pueden ser usuarios, equipos o campañas
de la misma instancia como cualquier tipo de perfil de otras instancias del
fediverso.

# Campañas

Los equipos podrán crear campañas dentro de la plataforma, estas campañas
podrán pertecener a uno o más equipos.

Todos los contenidos creados en la plataforma se podrán vincular a una
campaña, ya sean contenidos creados por personas o por equipos (eventos,
artículos, denuncias, etc.).

Cuando una persona o equipo crea un contenido para una campaña, pero el
usuario no es editor de no de los equipos de la campaña, podrá pedir
la vinculación de ese contenido a la campaña, y un editor, o administrador,
de cualquier de los equipos dueños de esta campaña podrá aceptarlo o
rechazarlo.

Este sistema generará una página para cada campaña, con las actividades
y contenidos vinculados a esa campaña.

Las Campañas se federan a fediverso como un Group, permitiendo a cualquier
usuario del fediverso recibir todos estos contenidos.

Cuando un contenido se vincula a una campaña lo que se producirá en
ActivityPub es un impulso (Announce).

# Podcasts

Para el sistema para podcast se usarán los formatos usados por Castopood
(pendiente de estudio)

# Denuncia

Un apartado permitirá a cualquier ciudadano (a través de un usuario o un
equipo) realizar una denuncia púbica, ya sea solo con un texto, o adjuntando material multimedia.

Cada denuncia de publicará en el fediverso como un artícuo (Article).

# Comentarios

Todos los contenidos de la plataforma tendrá la opción de recoger comentarios de los usuarios de la plataforma.

Los comentarios se implementarán a traves de notas (toots en Mastodon, Note
en ActivityPub)

# Fediverso

El fediverso es una alternativa distribuida a la redes sociales
centralizadas, donde cualquier persona puede crera instancias y las
instancias se comunican entre ella, si es la primera vez que escuchas hablar
del Fediverso te recomendamos la [ayuda de la campaña #VamosnosJuntas](https://vamonosjuntas.org/help)

Todos los activistas, equipos y las campañas, tendrán un usuario en el fediverso, podrán ser seguidos
desde cualquier instancia 
como Mastodón, pixelfed o PeerTube. De esta manera los contenidos, artículos, denuncias, podcasts, eventos,
etc. se distribuirán a través del fediverso. Estos contenidos por lo tanto podrán
ser comentados tanto por usuarios registrados en la plataforma como por cualquier usuario del fediverso.

Los usuarios, equipos y campañas podrán desde esta plataforma participar de fediverso, siguiendo otras cuentas
de la propia plataforma o de otras instancias del fediverso, contando con un timeline sin algoritmos.

Los contenidos generados por este software usan la propiedad Place de
ActivityPub para localizar geográficamente los contenidos, facilitando 
al menos las propiedades con las coordenadas gps y el código en geonames de
la localidad.

# Fronted de localidades

Cada localidad en cada instancia tiene una página pública donde muestra los
contenidos de esa localidad, Eventos (Agenda), Artículos, Denuncias, Podcasts, etc.

Se incorporarán los eventos de otras instancias que tengan la propiedad
Place. Las localidades tienen un código geoname, y a falta de este código se 
usarán las coordenadas gps y un radio en km para detectar que contenidos
publicados en otras instancias, se incorporan a la página de la localidad.

# Gobernanza y Moderación

La gobernanza de las instancias del fediverso es una de los principales
retos tecnopolíticos actualmente.

A un usuario de una instancia no se le puede pedir la participación en toda la
gestión (técnica, administrativa, moderación, etc.) por lo que inevitablemente existirán 
distintos grades de implicación.

De los participantes en una instancia destinada a activistas y movimientos
sociales esperamos un mayor grado de implicación, por lo que 
es un espacio en principio faborable para poner en marcha herramientas participativas de
moderación.

Es indispensable contar en último lugar con un espacio asambleario que
supervise, analize y reflexione sobre las posibles dinámicas tóxicas 
o métodos de abuso y espacios más humanos y directos a los que acudir si una
persona siente que está siendo perjudicada de alguna manera y los métodos de moderación implementados
no solucionan ese problema o no están funcionando correctamente.

También es importanta realizar una reflexión colectiva de análisis y evaluzación
de las dinámicas comunicativas que se generan, pero esto sería offline.

La moderación consiste princialmente en impedir que
determinado contenido no apropiado se pueda ver, esta decisión puede ser
temporal o permanente, puede ser sobre un cotenido, sobre un usuario, o
sobre una instancia, y puede ser un bloqueo total, o solo un silenciamiento.

Todo proceso de moderación se inicia con la denuncia de un contenido
concreto. Cualquier usuario puede denunciar un contenido independientemente
de si se trata de un contenido de nuestra instancia o importada de otra
instancia. A quien denuncia un contenido se le permitirá bloquear al usuario
que ha generado ese cotenido, de esta manear no volverá a ver contenidos de
este usuario.

Una vez se incorpora la denuncia a una base de datos de denuncias esta
denuncia deb ser rehazada o aceptada, y en el caso de ser aceptada
determinar las concescuencia, principalmente bloqueo de del contenido.

Se deben establecer distintos grados de gravedad, de manera que repetidos
bloqueos de contenidos provoquen un bloqueo del usuario.

El sitema debe contar con la posibilidad de que cualquier usuario pueda
incribirse como moderador y participar de estas decisiones. Estos usuarios 
tienen que valorar los incidentes que se le asginen, es decir, no pueden
acceder directamente para participar de la incidencia de su preferencia, y
además deben mantener cierto compromiso de participación (X evaluaciones a
la semana)

Todas las incidencias deben ser evaluadas por más de un usuario, y
al menos uno de ellos debe ser un usuario con una trayectoria de
evaluaciones certeras.

Se considera que un moderador tiene una trayectoria de evaluaciones certera
si normalmente coinciden sus evalucación con las evaluaciones de otros
usuarios a los mismos contenidos.

Este sistema sin supervisión podría provocar vicios y que una grupo
con una visión determinada tomara el control del sistema de moderación,
provocando que los que moderan corectamente sean minoría y por lo tanto el
sistema considere que no tiene una trayectoria de evaluaciones certeras.

Para evitar esta situación, se puede crear una segunda capa de moderación donde se revisen
las evaluaciones que no hayan sido unánimes.

En cualquier caso serán necesarios espacios asamblearios de supervisión más humanos y no
automatizados que supervisen los conflictos e intervengan si fuese
necesario, así como que puedan cambar parámetros de la configuración del
sistema de moderación, como cuantos usuarios deben evaluar cada denuncia,
que número de evaluaciones, y que proporción de ellas son correctas,
consideran que un moderador tiene una trayectoria de
evaluaciones certeras.

Sería interesante que las evaluaciones se resolvieran por concenso, y disponer de algún tipo
de mecanismo que se pusiera en marcha si alguna denuncia no se resuelve por una amplia
mayoría elevándolo a otro mecanismo de resolución.

https://www.colorado.edu/lab/medlab/2024/12/18/how-build-governable-spaces-online-communities
https://www.contributor-covenant.org/


