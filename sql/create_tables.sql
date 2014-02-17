drop table stations;
CREATE TABLE `stations` (
      `station_id` int(6) NOT NULL,
      `name` text NOT NULL,
      `latitude` decimal(14,10) NOT NULL,
      `longitude` decimal(14,10) NOT NULL,
      PRIMARY KEY (`station_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

drop table availabilitys;
CREATE TABLE `availabilitys` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `station_id` int(4) unsigned NOT NULL,
      `status_key` int(3) unsigned NOT NULL,
      `total_docks` int(3) unsigned NOT NULL,
      `available_bikes` int(3) unsigned NOT NULL,
      `timestamp` timestamp NOT NULL,
      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

drop table trips;
CREATE TABLE `trips` (
      `trip_id` int(11) NOT NULL,
      `depart` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
      `arrive` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
      `bike_id` int(10) unsigned NOT NULL,
      `duration` int(20) unsigned NOT NULL,
      `origin` int(4) unsigned NOT NULL,
      `destination` int(4) unsigned NOT NULL,
      `user` text,
      `gender` text,
      `birthyear` text,
      PRIMARY KEY (`trip_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
