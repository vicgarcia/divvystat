#!/usr/bin/env bash

# download the database from production via http
wget -O /tmp/divvystat.sql.gz http://divvystat.us/divvystat.sql.gz
cd /tmp && gunzip -f divvystat.sql.gz

# import the production data into mysql in docker container
mysql -u root -proot divvystat < /tmp/divvystat.sql