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
