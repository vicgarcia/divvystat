-- drop the existing database :
-- mysql -u <user> -p<pass> -e 'drop database divvystat'

-- create the database :
-- mysql -u <user> -p<pass> -e 'create database divvystat'

-- run this script to populate db structure :
-- mysql -u <user> -p<pass> <db name> < initial_db.sql

DROP TABLE IF EXISTS stations;
CREATE TABLE stations (
      landmark char(10) not null,
      name text not null,
      latitude decimal(14,10) not null,
      longitude decimal(14,10) not null,
      PRIMARY KEY stations__landmark__index (landmark)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS availabilitys;
CREATE TABLE availabilitys (
      id int(11) unsigned not null AUTO_INCREMENT,
      timestamp timestamp not null default CURRENT_TIMESTAMP,
      landmark char(10) not null,
      status_key int(3) unsigned not null,
      total_docks int(3) unsigned not null,
      available_bikes int(3) unsigned not null,
      PRIMARY KEY (id),
      KEY availabilitys__landmark__index (landmark),
      KEY availabilitys__timestamp__index (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;