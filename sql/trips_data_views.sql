
create view trips_rents_view as
    select
        origin as 'station_id',
        date_format(depart,'%w') as 'day',
        round(count(trip_id)/count(distinct date_format(depart, '%j'))) as 'rents'
    from trips
    group by origin, day
    order by origin, day;

create view trips_returns_view as
    select
        destination as 'station_id',
        date_format(arrive,'%w') as 'day',
        round(count(trip_id)/count(distinct date_format(arrive, '%j'))) as 'returns'
    from trips
    group by destination, day
    order by destination, day;
