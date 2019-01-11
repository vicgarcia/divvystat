#!/usr/bin/env bash

# wait for mysql to start before starting the php dev server
until mysqladmin -h mysql -u root -proot ping &> /dev/null; do
    echo "waiting for mysql to start ..." && sleep 10
done
echo "starting php development server"

# start php cli dev server
cd /code
php -S 0.0.0.0:8000 -t /code/public