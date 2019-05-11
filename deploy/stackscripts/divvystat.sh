#!/bin/bash
# https://www.linode.com/docs/platform/stackscripts/

# deploy divvystat.us production server w/ nginx, mysql, php7, and redis

# deployment variables
# <UDF name="mysql_root_password" label="mySQL root password" default="" />
# <UDF name="mysql_app_password" label="mySQL application password (use default)" default="divvystat" />
# <UDF name="vicgarcia_password" label="vicgarcia user account password" default="" />

# redirect all output to a log file at /root/deploy.log
exec >/root/deploy.log 2>&1

# use ubuntu core functions from stackscript
source <ssinclude StackScriptID=392468>

# noninteractive to prevent prompts during installs
export DEBIAN_FRONTEND=noninteractive

# configure the os
system_basic_setup
system_set_timezone "America/Chicago"
system_set_hostname "divvystat.us"

# add vicg4rcia user
system_add_sudoer "vicgarcia" "$VICGARCIA_PASSWORD"

# install mysql
mysql_install "$MYSQL_ROOT_PASSWORD"
mysql_create_database "$MYSQL_ROOT_PASSWORD" "divvystat"
mysql_create_user "$MYSQL_ROOT_PASSWORD" "divvystat" "$MYSQL_APP_PASSWORD"
mysql_grant_user "$MYSQL_ROOT_PASSWORD" "divvystat" "divvystat"

# install redis
redis_install

# install nginx
nginx_install
certbot_install

# install php 7.1 for divvystat
echo -e "\ninstall php 7.1 ..."

# install php 7.1 and necessary extensions
add-apt-repository -y ppa:ondrej/php
apt-get install -y php7.1-fpm php7.1-cli php7.1-dev php7.1-curl php7.1-mysql php7.1-mcrypt php-redis

# install composer globally
curl -o /tmp/composer-setup.php https://getcomposer.org/installer
curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig
php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }"
php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer --snapshot
rm -f /tmp/composer-setup.*

# add divvystat app user account
useradd --system divvystat

# install divvystat app code from github
mkdir /opt/divvystat
chown -R divvystat:divvystat /opt/divvystat
su - divvystat -c "git clone https://github.com/vicgarcia/divvystat.git /opt/divvystat"
echo

# install php dependencies w/ composer
su - divvystat -c "cd /opt/divvystat && composer install && echo"

# generate ssl certificate for divvystat.us
# XXX run this manually to avoid rate limit issues
# certbot --nginx -d divvystat.us -d www.divvystat.us -m vicg4rcia@gmail.com --agree-tos -n
# check this at : https://www.ssllabs.com/ssltest/analyze.html?d=divvystat.us

# configure divvystat
sed -i 's/redis/localhost/' /opt/divvystat/config/redis.php
sed -i 's/mysql/localhost/' /opt/divvystat/config/mysql.php

# use initial data script and update stations from divvy website
# mysql -u root -p$MYSQL_ROOT_PASSWORD divvystat < /opt/divvystat/deploy/initial_db.sql
# su - divvystat -c "cd /opt/divvystat && php divvystat.php update_stations"

# use backup bootstrap'd from the old server
wget -O /tmp/divvystat.sql.gz http://old.divvystat.us/divvystat.sql.gz
pushd /tmp && gunzip -f divvystat.sql.gz && popd
mysql -u root -p$MYSQL_ROOT_PASSWORD divvystat < /tmp/divvystat.sql

# setup cron jobs for divvystat user
crontab -l -u divvystat | cat - /opt/divvystat/deploy/crontab | crontab -u divvystat -

# add nginx config for divvystat.us
cp /opt/divvystat/deploy/nginx.conf /etc/nginx/sites-available/divvystat.us
# XXX run this manually after generating ssl to avoid errors
# ln -s /etc/nginx/sites-available/divvystat.us /etc/nginx/sites-enabled/

# to enable certbot renew via a cron job, in root cron add ...
# 0 5 1 */3 * echo -e "\n$(date)" >> /opt/divvystat/certbot-renew.log && certbot renew --dry-run >> /opt/divvystat/certbot-renew.log
# create an empty file to use for log output from certbot
# touch /opt/divvystat/certbot-renew.log

# configure firewall
ufw allow "OpenSSH"
ufw allow "Nginx Full"
ufw enable

# disable root login via ssh
system_disable_root_ssh

# stop nginx and wait for reboot
service nginx stop
echo -e "\ndivvystat.us automated install complete"
echo -e "\ncomplete manual configuration and reboot"
echo -e "\n - run certbox command to generate ssl"
echo -e "\n - activate nginx virtual host config"
