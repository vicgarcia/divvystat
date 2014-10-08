<?php
namespace dvMap;

class DB
{
    protected $db;

    public function __construct(\MeekroDB $db)
    {
        $this->db = $db;
    }

    public function getStationIds()
    {
        $sql = "select station_id from stations";
        $stationIds = $this->db->queryOneColumn('station_id', $sql);

        return $stationIds;
    }

    public function getStationsData()
    {
        $sql = "select * from overview";
        $stations = $this->db->query($sql);

        return $stations;
    }

    public function get72HourStationLine($stationId, \DateTime $end = null)
    {
        // default endtime to now if not explicitly provided
        if ($end == null)
            $end = new \DateTime("now");

        // subtract 72 hours to get the start time
        $start = clone $end;
        $start->sub(new \DateInterval("PT72H"));

        $sql = "
            select timestamp, available_bikes as 'bikes'
            from availabilitys
            where station_id = %i
              and timestamp between %t and %t
            order by timestamp desc
            ";
        $rows = $this->db->query($sql, $stationId, $start, $end);
        $timeline = array();

        $prev = null;
        foreach ($rows as $row) {
            // if the # of bikes has changed since previous datapoint
            if ($row['bikes'] != $prev) {
                $timeline[] = $row;
                $prev = $row['bikes'];
            }
        }

        return $timeline;
    }

    public function get72HourOutageLine(\DateTime $end = null)
    {
        // default endtime to now if not explicitly provided
        if ($end == null)
            $end = new \DateTime("now");

        // subtract 72 hours to get the start time
        $start = clone $end;
        $start->sub(new \DateInterval("PT72H"));

        $sql = "
            select timestamp, station_count as 'outages'
            from outages
            where station_id = %i
              and timestamp between %t and %t
            order by timestamp desc
            ";
        $rows = $this->db->query($sql, $start, $end);
        $timeline = array();

        $prev = null;
        foreach ($rows as $row) {
            // if the # of station outages changed since previous datapoint
            if ($row['outages'] != $prev) {
                $timeline[] = $row;
                $prev = $row['outages'];
            }
        }

        return $timeline;
    }

    public function getRecentUsageGraph($stationId)
    {
        $rawDataSql = "
            select
              date_format(a.timestamp, '%j') as 'day',
              date_format(a.timestamp,'%w') as 'day_of_week',
              a.timestamp,
              a.available_bikes
            from availabilitys a
            where a.station_id = %i
              and DATE(a.timestamp) between DATE(DATE_SUB(NOW(), INTERVAL 31 day))
                                        and DATE(DATE_SUB(NOW(), INTERVAL 1 day))
            order by a.timestamp asc
            ";
        $rows = $this->db->query($rawDataSql, $stationId);

        // populate initial day of week containers
        $days = [];
        $counts = [];
        foreach (range(0, 6) as $dayOfWeek) {
            $days[$dayOfWeek] = [];
            $counts[$dayOfWeek] = 0;
        }

        // parse usage (changes in count) to day of week and track dates (for avg)
        $previous = $rows[0]['available_bikes'];
        foreach ($rows as $row) {
            if ($row['available_bikes'] < $previous) {
                $days[$row['day_of_week']][] = $row['day'];
                $counts[$row['day_of_week']] += ($previous - $row['available_bikes']);
            }
            $previous = $row['available_bikes'];
        }

        // collate by day of week and calculate averages as counts / dates
        $usageByWeekday = [];
        foreach ($days as $ofWeek => $inResults) {
            $day = $this->dayOfWeekMap()[$ofWeek];
            $usageByWeekday[$ofWeek]['day'] = $day;

            $usage = $counts[$ofWeek] / count(array_unique($inResults));
            $usageByWeekday[$ofWeek]['usage'] = (string) $usage;
        }

        return $usageByWeekday;
    }

    public function getRecentOutageGraph($stationId)
    {


    }

    public function insertAvailability($id, $status, $docks, $bikes, $timestamp)
    {
        return $this->db->insert('availabilitys', [
            'station_id'      => $id,
            'status_key'      => $status,
            'total_docks'     => $docks,
            'available_bikes' => $bikes,
            'timestamp'       => $timestamp
        ]);
    }

    public function insertOutage($stations, $detail, $timestamp)
    {
        return $this->db->insert('outages', [
            'station_count' => $stations,
            'detail'        => $detail,
            'timestamp'     => $timestamp
        ]);
    }

    public function insertUpdateStation($id, $name, $lat, $lng)
    {
        return $this->db->insertUpdate('stations', [
            'station_id' => $id,
            'name'       => $name,
            'latitude'   => $lat,
            'longitude'  => $lng
        ]);
    }

    protected function dayOfWeekMap()
    {
        return array(
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday'
        );
    }
}
