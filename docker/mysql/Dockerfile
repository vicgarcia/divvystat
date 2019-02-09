FROM mysql:5.7
RUN apt-get update
# install wget for use downloading/importing dev database content
RUN apt-get install -y wget
# copy init script to container
COPY docker/mysql/import.sh /docker-entrypoint-initdb.d/