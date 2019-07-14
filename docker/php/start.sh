#!/usr/bin/env bash

# wait for mysql to start before starting the php dev server
until mysqladmin -h mysql -u root -proot ping &> /dev/null; do
    echo "waiting for mysql to start" && sleep 30
done
echo "starting php development server"

# initialize database with initial_db.sql script
# use this to overwrite the database dump installed at startup
# mysql --host mysql --port 3306 -u root -p$MYSQL_ROOT_PASSWORD < /code/deploy/initial_db.sql

cd /code

# install php dependencies via composer
composer install

# install grunt dependencies via npm
npm install

# build the javascript (rebuild during development w/ docker script and grunt watch)
# grunt build

# run the php dev server
php -S 0.0.0.0:8000 -t /code/public