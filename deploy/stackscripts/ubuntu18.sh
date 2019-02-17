#!/bin/bash

# basic functions for reuse in server specific ubuntu stackscripts

# much of this is copied from https://www.linode.com/stackscripts/view/1
# opted to copy vs import due to outdated features in the linode stackscript

# update the os
function system_update {
    echo -e "\nsystem update ..."

    apt-get update
    apt-get -y upgrade
}

# update the os and install basics
function system_basic_setup {
    echo -e "\nupdate system and install basics ..."

    # system update
    apt-get update
    apt-get -y upgrade

    # install common linux tools
    apt-get -y install build-essential python-software-properties
    apt-get -y install git vim tmux ack zip unzip wget curl nmap netcat
}

# set the system timezone
function system_set_timezone {
    echo -e "\nsetting timezone ..."

    # available timezones : http://manpages.ubuntu.com/manpages/bionic/man3/DateTime::TimeZone::Catalog.3pm.html

    # $1 - timezone to set
    if [ ! -n "$1" ]; then
        echo "requires the timezone as its first argument"
        return 1;
    fi

    ln -snf /usr/share/zoneinfo/$1 /etc/localtime && echo $1 > /etc/timezone
}

# set the system hostname
function system_set_hostname {
    echo -e "\nsetting hostname ..."

    # $1 - hostname to set
    if [ ! -n "$1" ]; then
        echo "requires the hostname as its first argument"
        return 1;
    fi

    echo "$1" > /etc/hostname
    hostname -F /etc/hostname
}

# add user w/ sudo privileges password
function system_add_sudoer {
    echo -e "\nadding new user as sudoer ..."

    # $1 - the user account to add
    if [ ! -n "$1" ]; then
        echo "requires the username as its first argument"
        return 1;
    fi

    # $2 - the password for the user account
    if [ ! -n "$1" ]; then
        echo "requires the user password as its second argument"
        return 1;
    fi

    useradd -m -s /bin/bash -G sudo $1
    echo "$1:$2" | chpasswd
}

# get the primary ip for the system
function system_primary_ip {
    # returns the primary IP assigned to eth0
    echo $(ifconfig eth0 | awk -F: '/inet addr:/ {print $2}' | awk '{ print $1 }')
}

# disable ssh access from root account
function system_disable_root_ssh {
    # disables root SSH access
    sed -i 's/PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config
}

# install mysql and configure root user password
function mysql_install {
    echo -e "\ninstall mysql ..."

    # $1 - the mysql root password
    if [ ! -n "$1" ]; then
        echo "mysql_install() requires the root pass as its first argument"
        return 1;
    fi

    echo "mysql-server mysql-server/root_password password $1" | debconf-set-selections
    echo "mysql-server mysql-server/root_password_again password $1" | debconf-set-selections
    apt-get -y install mysql-server mysql-client

    echo "sleeping while MySQL starts up for the first time ..."
    sleep 10
}

# create mysql database
function mysql_create_database {

    # $1 - the mysql root password
    if [ ! -n "$1" ]; then
        echo "mysql_create_database() requires the root pass as its first argument"
        return 1;
    fi

    # $2 - the db name to create
    if [ ! -n "$2" ]; then
        echo "mysql_create_database() requires the name of the database as the second argument"
        return 1;
    fi

    echo "CREATE DATABASE $2;" | mysql -u root -p$1
}

# create mysql user with password
function mysql_create_user {

    # $1 - the mysql root password
    if [ ! -n "$1" ]; then
        echo "mysql_create_user() requires the root pass as its first argument"
        return 1;
    fi

    # $2 - the user to create
    if [ ! -n "$2" ]; then
        echo "mysql_create_user() requires username as the second argument"
        return 1;
    fi

    # $3 - their password
    if [ ! -n "$3" ]; then
        echo "mysql_create_user() requires a password as the third argument"
        return 1;
    fi

    echo "CREATE USER '$2'@'localhost' IDENTIFIED BY '$3';" | mysql -u root -p$1
}

# grant mysql user permission on a database
function mysql_grant_user {

    # $1 - the mysql root password
    if [ ! -n "$1" ]; then
        echo "mysql_create_user() requires the root pass as its first argument"
        return 1;
    fi

    # $2 - the user to bestow privileges
    if [ ! -n "$2" ]; then
        echo "mysql_create_user() requires username as the second argument"
        return 1;
    fi

    # $3 - the database
    if [ ! -n "$3" ]; then
        echo "mysql_create_user() requires a database as the third argument"
        return 1;
    fi

    echo "GRANT ALL PRIVILEGES ON $3.* TO '$2'@'localhost';" | mysql -u root -p$1
    echo "FLUSH PRIVILEGES;" | mysql -u root -p$1
}

# install postgres
function postgres_install {
    echo -e "\ninstall postgresql ..."

    apt-get -y install postgresql
    echo "sleeping while PostgreSQL starts up for the first time ..."
    sleep 10
}

# create postgres database
function postgres_create_database_and_user {

    # $1 - the database to create
    if [ ! -n "$1" ]; then
        echo "postgres_create_database_and_user() requires the database name as its first argument"
        return 1;
    fi

    # $2 - the user to create
    if [ ! -n "$2" ]; then
        echo "postgres_create_database_and_user() requires the user name as its second argument"
        return 1;
    fi

    # $3 - the password for the user
    if [ ! -n "$3" ]; then
        echo "postgres_create_database_and_user() requires the password as its third argument"
        return 1;
    fi

    su - postgres -c "psql -c \"CREATE USER $2 WITH PASSWORD '$3'\""
    su - postgres -c "psql -c \"CREATE DATABASE $1 WITH OWNER $2\""
}

# install redis
function redis_install {
    echo -e "\ninstall redis ..."

    apt-get -y install redis-server
}

# install nginx
function nginx_install {
    echo -e "\ninstall nginx ..."

    apt-get -y install nginx

    # update dhparams (this runs for a while)
    pushd /etc/nginx
    openssl dhparam -out dhparams.pem 2048
    popd
}

# install certbot
function certbot_install {
    echo -e "\ninstall certbot ..."

    # https://www.digitalocean.com/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-18-04

    add-apt-repository -y ppa:certbot/certbot
    apt-get install -y python-certbot-nginx
}

# install supervisor
function supervisor_install {
    echo -e "\ninstall supervisor.d ..."

    # https://www.digitalocean.com/community/tutorials/how-to-install-and-manage-supervisor-on-ubuntu-and-debian-vps

    apt-get -y install supervisor
}

# install python 3 on ubuntu 18
function python3_install {
    echo -e "\ninstall python3 ..."

    # install python3 + pipenv
    apt-get -y install python3-dev python3-pip
    pip3 install --upgrade pip setuptools pipenv
}
