# wtg-test
cd src

ducker-compose up

After launch:

docker exec -it WTG_test-php-fpm bash

composer update

npm install

php artisan optimize

php artisan cache:clear

php artisan migrate

After relaunch docker-compose

http://localhost:8080/chat
