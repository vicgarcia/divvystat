Divvystat is a simple web application to scrape data and present graphs showing recent bike share usage from the City of Chicago's Divvy bike share system. This application was originally created for the 2014 Divvy Data Challenge.

See it in action at http://divvystat.us

![divvystat.us screenshot](https://raw.githubusercontent.com/vicgarcia/divvystat/master/public/screenshot.png)

<br />

clone the repository

```
git clone git@github.com:vicgarcia/divvystat.git
cd divvystat
```

build the application container

```
docker-compose build
```

start the docker containers (php/redis/mysql)

```
docker-compose up
```

the mysql container will download a database backup from divvystat.us to import

once the containers are running visit http://localhost:8000

access redis or mysql via cli

```
docker/bin/redis
docker/bin/mysql
```

run divvystat tasks via cli

```
docker/bin/divvystat <record_data|update_stations|prune_data|clear_cache>
```

run grunt build tool to compile js/sass

```
docker/bin/grunt build
```

get a shell inside the running docker container

```
docker/bin/bash
```
