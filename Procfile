web: vendor/bin/heroku-php-apache2 public/
worker: php artisan queue:work database --queue=syncproducto,procesarproducto,procesarstock --tries=3 --sleep=5 --max-time=3600