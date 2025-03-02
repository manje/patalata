# patalata

Patalata es una herramienta basada en ActivityPub, diseñada para conectar a las
comunidades locales y potenciar la participación en movimientos sociales y
de barrio. Además de las funcionalidades tradicionales de ActivityPub, Patalata se centra
en la asociación de los contenidos a una localidad, permitiendo a los usuarios
descubrir eventos, podcasts, artículos y otras actividades asociadas a su localidad.

# Requisitos del Sistema

El sistema está basado en Laravel 12, por lo que necesitará al menos la
versión 8.2 de PHP.

Tenemos pendiente hacer la instalación en máquinas recién instaladas con las
últimas versiones de Debian y Ubuntu para testear que funciona y comprobar
las dependencias necesarias.

# Instalación

patalata está desarrollado en Laravel, por lo que que puede instalarse
sobre distintos servidores web, sistemas de correo, almacenamiento, cache y base de
datos.

Estos pasos instalan la aplicación en un servidor web nginx o Apache, usando de base de
datos MariaDB o MySql, con almacenamiento y correo local.

Si está familizarizado con el entorno Laravel el despliegue de la aplicación
sigue el método estandar.

# Base de datos

El proceso con MariaDB o MySql es prácticamente igual debemos
contar con acceso a una base de datos ya creada, si tiene que crearla estos
comandos serían suficiente:

```sh

create database patalata;
grant all privileges on patalata.* to patalata@localhost identified by '--password--';
flush privileges;

```

Esto creará la base de datos patalata y un usuario también llamado patalata
con permisos en esa base de datos.

Si desea usar PostgreSQL debes instalar la extensión PostGIS.

# Descarga

En este ejemplo, vamos a instalar la aplicación en /var/www, si desea
hacerlo en otra localización solo debe cambiar las rutas.

```sh
cd /var/www
git clone https://github.com/manje/patalata.git
cd patalata
cp env.example .env
```

# Configuración

Edite el fichero .env , y modifique lo necesario, especialmente tendrá
que establecer APP_NAME y APP_URL además de las constantes que comienzan por
BD_ donde debes indicar las credenciales para la conexión a la base de
datos.

# Despliegue

Una vez configurado use los siguientes comandos para desplegar la
aplicación:

```sh

composer install
npm install
npm run build
php artisan migrate
php artisan key:generate
php artisan storage:link

```

También deberá lanzar el comando que ejecutar los trabajos, puede hacerlo
simplemente así:

```sh

nohup php artisan queue:work > storage/logs/work.log &

```

Este proceso debe estar activo siempre, entre otras cosas, para distribuir
los contenidos a otras instancias federadas.

# Nginx/Apache

Estos pasos deberá hacerlo con el usuario root, debe tener configurado en el
servidor 

## Nginx

Debe crear un fichero en /etc/nginx/sites-available/ , por ejemplo
/etc/nginx/sites-available/patalata y después hacer:

```sh
ln -s /etc/nginx/sites-available/travellist /etc/nginx/sites-enabled/
```

```sh
server {
    listen 80;
    server_name server_domain_or_IP;
    root /var/www/patalata/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.htm index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

* Actualización

Puedes crear un script para automatizar la actualizació, pues se trataría
solo de estos comandos

```sh

php artisan down
git pull
composer install
php artisan migrate --force
npm ci
npm run build
php artisan up

```
