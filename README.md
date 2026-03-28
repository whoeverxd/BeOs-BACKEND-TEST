# Descripción del proyecto
    API RESTful desarrollada en Laravel para la gestión de productos y sus precios en múltiples divisas. Permite operaciones CRUD y manejo de conversiones utilizando tasas de cambio almacenadas en base de datos.


# Decisiones técnicas
    - He Cambiado los nombre de los modelos a ingles para mantener la coherencia, ya que los campos estan en español. Suelo ser mas purista y respetar las instrucciones y nombres del proyecto pero en este caso me di la libertad para lograr algo mas elegante.

# Estructura del proyecto
    project/
    │
    ├── app/
    ├── database/
    ├── routes/api.php
    │
    ├── docs/
    │   ├── postman_collection.json

    │
    ├── README.md


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

# Explicación de las relaciones
    Product → Currency
        Cada producto tiene una divisa base (currency_id).
    Product → ProductPrice
        Cada producto puede tener muchos precios en diferentes divisas.
    ProductPrice → Currency
        Cada precio está asociado a una divisa específica.
    Currency → Product/ProductPrice
        Una divisa puede ser base de muchos productos y usada en muchos precios.

# Cómo correr el proyecto
    composer install
    cp .env.example .env
    php artisan key:generate
    php artisan migrate
    php artisan db:seed #He creado productos de prueba 
    php artisan serve


Notas
• La API fue creada con laravel 12.56
• La API usa Eloquent para interactuar con la base de datos.
• La API debe devolver los datos en formato JSON.
• La API debe tener una documentación clara y concisa.


# DOCUMENTACION

    - para la documentacion se ha añadido swagger al proyecto y se han documentado las rutas
    - se ha añadido tambien la carpeta /docs con el "export" de Postman



    