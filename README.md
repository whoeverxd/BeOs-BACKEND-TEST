Descripción del proyecto
API RESTful desarrollada en Laravel para la gestión de productos y sus precios en múltiples divisas. Permite operaciones CRUD y manejo de conversiones utilizando tasas de cambio almacenadas en base de datos.


Decisiones técnicas
    - He Cambiado los nombre de los modelos a ingles para mantener la coherencia, ya que los campos estan en español. Suelo ser mas purista y respetar las instrucciones y nombres del proyecto pero en este caso me di la libertad para lograr algo mas elegante.

Estructura del proyecto



Modelos 
+----------------+           +----------------+           +-------------------+
|    Currency    |           |     Product    |           |   ProductPrice    |
+----------------+           +----------------+           +-------------------+
| id             |<--------->| currency_id    |           | id                |
| name           |  belongsTo| id             |<--------->| product_id        |
| symbol         |  (Product)| name           | hasMany   | currency_id       |
| exchange_rate  |           | description    |           | price             |
+----------------+           | price          |           +-------------------+
                             | tax_cost       |
                             | manufacturing_cost |
                             +----------------+

Explicación de las relaciones
    Product → Currency
        Cada producto tiene una divisa base (currency_id).
    Product → ProductPrice
        Cada producto puede tener muchos precios en diferentes divisas.
    ProductPrice → Currency
        Cada precio está asociado a una divisa específica.
    Currency → Product/ProductPrice
        Una divisa puede ser base de muchos productos y usada en muchos precios.

Cómo correr el proyecto
    composer install
    cp .env.example .env
    php artisan key:generate
    php artisan migrate
    php artisan serve
