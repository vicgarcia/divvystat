#!/usr/bin/env bash

# wait for mysql to start before starting the php dev server
until mysqladmin -h mysql -u root -proot ping &> /dev/null; do
    echo "waiting for mysql to start" && sleep 30
done
echo "starting php development server"

cd /code

# install php dependencies via composer
composer install

# install grunt dependencies via npm
npm install

# build the javascript (rebuild during development w/ docker script and grunt watch)
# grunt build

# run the php dev server
php -S 0.0.0.0:8000 -t /code/public