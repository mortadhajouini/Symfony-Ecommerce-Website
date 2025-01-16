https://wampserver.aviatechno.net/

http://localhost/phpmyadmin/
root

install composer


https://symfony.com/download install symfony cli (env)

new project: symfony new --webapp my_project

run the server: symfony server:start

user: php bin/console make:user
symfony console make:auth
php bin/console make:controller
php bin/console make:entity

php bin/console make:migration
php bin/console doctrine:migrations:migrate
php bin/console doctrine:schema:update --force

php bin/console doctrine:database:create


composer require vich/uploader-bundle
