web: vendor/bin/heroku-php-apache2 public/
worker: php artisan queue:restart && php artisan queue:work database --queue=syncproducto,procesarproducto,procesarstock --tries=3 --sleep=3 --max-time=3600