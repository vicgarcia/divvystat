-- run this script to re-populate db structure :
-- mysql -u <user> -p<pass> < initial_db.sql

DROP DATABASE divvystat;

CREATE DATABASE divvystat;

USE divvystat;

DROP TABLE IF EXISTS stations;
CREATE TABLE stations (
      id int(11) unsigned not null,
      name text not null,
      latitude decimal(14,10) not null,
      longitude decimal(14,10) not null,
      PRIMARY KEY stations__id__index (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS availabilitys;
CREATE TABLE availabilitys (
      id int(11) unsigned not null AUTO_INCREMENT,
      timestamp timestamp not null default CURRENT_TIMESTAMP,
      station int(11) unsigned not null,
      docks int(3) unsigned not null,
      bikes int(3) unsigned not null,
      PRIMARY KEY (id),
      KEY availabilitys__station__index (station),
      KEY availabilitys__timestamp__index (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
