# Fun with libsodium
Based on this article: https://hackernoon.com/how-to-store-encrypted-data-collected-by-your-web-application-with-php7-and-libsodium-ww1y32a5

This repository was created to understand and experiment with the paradigm of
storing sensitive user data and at the same time be able to query it.

## Up and Running
+ Run `composer install`
+ Make shure you have a db running and insert connection details in .env file
+ Run `php artisan migrate` to create necessary db tables.
+ Run `php artisan app:generate-order-keys` and insert the keys in your .env file under: "ORDER_PRIVATE_KEY=[Private key]" and "ORDER_INDEX_KEY=[Index key]"
+ Run `./vendor/bin/phpunit` to see that the orders are sored and can be queried

## Interesting files in this repository
+ [2023_09_11_183221_create_orders_table.php](database/migrations/2023_09_11_183221_create_orders_table.php)
+ [Order.php](app/Models/Order.php)
+ [GenerateOrderKeys.php](app/Console/Commands/GenerateOrderKeys.php)
+ [OrderTest.php](tests/Unit/OrderTest.php)
