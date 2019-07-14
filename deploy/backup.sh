#!/bin/bash

# replace the PASSWORD in the mysqldump command for use in production

# clear the old backup
rm -rf /opt/divvystat/public/divvystat.sql.gz

# backup the databases
mysqldump -u divvystat -pdivvystat divvystat | gzip > /opt/divvystat/public/divvystat.sql.gz
