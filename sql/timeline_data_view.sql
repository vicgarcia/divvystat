create index availabilitys__timestamp__index on availabilitiys (timestamp);
create view timeline_view as
    select
        station_id as 'id',
        timestamp as 'timestamp',
        available_bikes as 'bikes'
    from availabilitys
    where timestamp > timestampadd(hour, -72, now())
    order by timestamp desc;
