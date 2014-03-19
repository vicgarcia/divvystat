create view weekly_averages_view as
      select
        s.station_id as 'station_id',
        ( select sum(rents)
          from trips_rents_view
          where station_id = s.station_id
        ) as 'avg_rents_per_week',
        ( select sum(returns)
          from trips_returns_view
          where station_id = s.station_id
        ) as 'avg_returns_per_week'
      from stations s
