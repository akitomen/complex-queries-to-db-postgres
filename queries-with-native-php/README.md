# Complex queries to database (Postgres).
1. Warehouse and ecommerce complex solution for data retrieving (Laravel + Postgres and Php native app + Postgres)

* Php version - 8.*;
* Postgres version - 13.*
* Laravel version - 8.4

## Native php vesion : 

* Made in Controller class (where all business logic is), Repository class (where all database requests are), Views class: used to call views from the Views folder. (It was all about the classes catalog).
* Public directory - there is index.php (entry point), front files app.js and app.css, and htacess for apache configurations
* The routes.php file contains an array with guest routes
* example.config.php file should be named config.php and write all parameters for your postgres database connection
* All database with structure and data can be imported from db.sql


## Laravel version :

* The structure of Laravel is standard, except that all requests are located in the class queries-with-laravel\app\Repositories\PostgresRepository.php
* In addition, you need to install the vendor catalog with the php dependency: composer install
* migration can be done with the command php artisan migrate:refresh -seed (all sql files are in the directory queries-with-laravel\database\dumps) (This should be done only after the composer install command)
* The .env.example file should be renamed to .env and the relevant parameters for postgres should be entered