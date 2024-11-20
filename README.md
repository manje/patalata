# patalata


Plataforma de movilización social



* Instalación

```sh
git clone https://github.com/manje/patalata.git

cp env.example .env
```



Editar .env, princpialmente los datos de conexión a la base de datos y APP_URL

```sh
cd patalata

composer install

npm install
npm run build

php artisan migrate
php artisan key:generate
php artisan municipios:importar
php artisan db:seed
php artisan storage:link

```

Si tienes una versión anterior al 21 Nov 2025, y la actualizas, ejecuta: php artisan generate:slugs