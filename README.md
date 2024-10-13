# patalata


Plataforma de movilización social



* Instalación

git clone https://github.com/manje/patalata.git
cp env.example .env

Editar .env, princpialmente los datos de conexión a la base de datos y APP_URL

composer update

npm install
npm run build
php artisan migrate

php artisan key:generate
