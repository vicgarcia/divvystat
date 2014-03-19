select
  sa.station_id,
  sa.station_name,
  sa.avg_rents_per_week,
  sa.avg_returns_per_week,
  sa.avg_rents_per_week / sa.avg_returns_per_week as rents_to_returns
from (
  select
    s.station_id as 'station_id',
    s.name as 'station_name',
    ( select sum(rents)
      from trips_rents_view
      where station_id = s.station_id
    ) as 'avg_rents_per_week',
    ( select sum(returns)
      from trips_returns_view
      where station_id = s.station_id
    ) as 'avg_returns_per_week'
  from stations s
) sa
order by rents_to_returns asc
