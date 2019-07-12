-- run this script to re-populate db structure :
-- mysql -u <user> -p<pass> < initial_db.sql

DROP DATABASE divvystat;

CREATE DATABASE divvystat;

USE divvystat;

DROP TABLE IF EXISTS stations;
CREATE TABLE stations (
      terminal char(20) not null,
      name text not null,
      latitude decimal(14,10) not null,
      longitude decimal(14,10) not null,
      PRIMARY KEY stations__terminal__index (terminal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS availabilitys;
CREATE TABLE availabilitys (
      id int(11) unsigned not null AUTO_INCREMENT,
      timestamp timestamp not null default CURRENT_TIMESTAMP,
      terminal char(20) not null,
      docks int(3) unsigned not null,
      bikes int(3) unsigned not null,
      PRIMARY KEY (id),
      KEY availabilitys__terminal__index (terminal),
      KEY availabilitys__timestamp__index (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
