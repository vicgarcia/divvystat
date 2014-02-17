create index availabilitys__station_id__index on availabilitiys (station_id);
create view station_view as
    select
        s.station_id as 'id',
        s.name as 'name',
        s.latitude as 'lat',
        s.longitude as 'lng',
        ( select available_bikes from availabilitys
          where station_id = s.station_id
          order by timestamp desc limit 1 ) as 'bikes',
        ( select total_docks from availabilitys
          where station_id = s.station_id
          order by timestamp desc limit 1 ) as 'docks'
    from stations s
    order by station_id desc;
