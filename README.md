Descripción del proyecto
API RESTful desarrollada en Laravel para la gestión de productos y sus precios en múltiples divisas. Permite operaciones CRUD y manejo de conversiones utilizando tasas de cambio almacenadas en base de datos.


Decisiones técnicas
    - He Cambiado los nombre de los modelos a ingles para mantener la coherencia, ya que los campos estan en español. Suelo ser mas purista y respetar las instrucciones y nombres del proyecto pero en este caso me di la libertad para lograr algo mas elegante.

Estructura del proyecto




Cómo correr el proyecto
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
