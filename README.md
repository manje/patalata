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
php artisan db:seed```