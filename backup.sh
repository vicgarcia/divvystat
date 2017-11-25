#!/bin/bash

# replace the password in the mysqldump command for use in production

# clear the old backup
rm -rf /opt/divvystat/divvystat.sql.gz

# backup the databases
mysqldump -u divvystat -ppassword divvystat | gzip > /opt/divvystat/divvystat.sql.gz
