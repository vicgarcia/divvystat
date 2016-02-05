drop table stations;
create table `stations` (
      `station_id` int(6) not null,
      `name` text not null,
      `latitude` decimal(14,10) not null,
      `longitude` decimal(14,10) not null,
      PRIMARY KEY `stations__station_id__index` (`station_id`)
) ENGINE=InnoDB default CHARSET=utf8;

drop table availabilitys;
create table `availabilitys` (
      `id` int(11) unsigned not null AUTO_INCREMENT,
      `timestamp` timestamp not null default CURRENT_TIMESTAMP,
      `station_id` int(4) unsigned not null,
      `status_key` int(3) unsigned not null,
      `total_docks` int(3) unsigned not null,
      `available_bikes` int(3) unsigned not null,
      PRIMARY KEY (`id`),
      KEY `availabilitys__station_id__index` (`station_id`),
      KEY `availabilitys__timestamp__index` (`timestamp`)
) ENGINE=InnoDB AUTO_INCREMENT=23429329 default CHARSET=utf8;

create index availabilitys__station_id__index on availabilitys (station_id);
create view overview as
    select
        s.station_id as 'id',
        s.name as 'name',
        s.latitude as 'lat',
        s.longitude as 'lng',
        ( select available_bikes from availabilitys
          where station_id = s.station_id
          order by id desc limit 1 ) as 'bikes',
        ( select total_docks from availabilitys
          where station_id = s.station_id
          order by id desc limit 1 ) as 'docks'
    from stations s
    order by station_id desc;
