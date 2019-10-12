<?php
namespace DivvyStat;

class DB
{
    protected $db;

    public function __construct(\MeekroDB $db)
    {
        $this->db = $db;
    }

    public function getCredentials()
    {
        return [
            'username' => $this->db->user,
            'password' => $this->db->password,
            'database' => $this->db->dbName
        ];
    }

    public function getStationIds()
    {
        $sql = "select id from stations";
        $ids = $this->db->queryOneColumn('id', $sql);

        return $ids;
    }

    public function getStations()
    {
        $sql = "select id, name, latitude, longitude from stations";
        $stations = $this->db->query($sql);

        return $stations;
    }

    public function getStationCapacity($stationId)
    {
        $sql = "
            select bikes, docks
            from availabilitys
            where station = %s
            order by id desc
            limit 1
        ";
        $stations = $this->db->queryFirstRow($sql, $stationId);

        return $stations;
    }

    public function getStationTimeline($stationId, \DateTime $end = null)
    {
        // default endtime to now if not explicitly provided
        if ($end == null)
            $end = new \DateTime("now");

        // subtract 72 hours to get the start time
        $start = clone $end;
        $start->sub(new \DateInterval("PT72H"));

        // query for points in interval
        $sql = "
            select timestamp, bikes
            from availabilitys
            where station = %s
              and timestamp between %t and %t
            order by timestamp desc
        ";
        $rows = $this->db->query($sql, $stationId, $start, $end);

        // collect points for display on the timeline
        $timeline = [];
        if (count($rows) > 2) {

            // add the first point to the timeline
            $timeline[] = $rows[0];
            $prev = $rows[0]['bikes'];

            // add intermediate points to the timeline
            for ($i = 1 ; $i < (count($rows) - 1) ; $i++) {
                // add if the # of bikes has changed since previous datapoint
                if ($rows[$i]['bikes'] != $prev) {
                    $timeline[] = $rows[$i];
                    $prev = $rows[$i]['bikes'];
                }
            }

            // add the last point to the timeline
            $timeline[] = $rows[count($rows) - 1];
        }

        return $timeline;
    }

    public function getStationGraph($stationId)
    {
        $rawDataSql = "
            select
              timestamp,
              date_format(timestamp, '%j') as 'day',
              date_format(timestamp,'%w') as 'day_of_week',
              bikes
            from availabilitys
            where station = %s
              and DATE(timestamp) between DATE(DATE_SUB(NOW(), INTERVAL 31 day))
                                      and DATE(DATE_SUB(NOW(), INTERVAL  1 day))
            order by timestamp asc
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
        if (count($rows) > 0) {
            $previous = $rows[0]['bikes'];
            foreach ($rows as $row) {
                if ($row['bikes'] < $previous) {
                    $days[$row['day_of_week']][] = $row['day'];
                    $counts[$row['day_of_week']] += ($previous - $row['bikes']);
                }
                $previous = $row['bikes'];
            }
        }

        // collate by day of week and calculate averages as counts / dates
        $usageByWeekday = [];
        foreach ($days as $ofWeek => $inResults) {
            $day = $this->dayOfWeekMap($ofWeek);
            $usageByWeekday[$ofWeek]['day'] = $day;
            if (count(array_unique($inResults)) != 0) {
                $usage = $counts[$ofWeek] / count(array_unique($inResults));
                $usageByWeekday[$ofWeek]['usage'] = (string) $usage;
            }
        }

        return $usageByWeekday;
    }

    public function getLatestUpdate()
    {
        $sql = "select max(timestamp) as 'timestamp' from availabilitys";
        $timestamp = $this->db->queryFirstField($sql);

        return $timestamp;
    }

    public function insertAvailability($station, $docks, $bikes, $timestamp)
    {
        return $this->db->insert('availabilitys', [
            'station'    => $station,
            'docks'      => $docks,
            'bikes'      => $bikes,
            'timestamp'  => $timestamp
        ]);
    }

    public function insertUpdateStation($id, $name, $latitude, $longitude)
    {
        return $this->db->insertUpdate('stations', [
            'id'         => $id,
            'name'       => $name,
            'latitude'   => $latitude,
            'longitude'  => $longitude
        ]);
    }

    public function pruneData()
    {
        $deleteAvails = "
            delete from availabilitys
            where timestamp < TIMESTAMP(DATE_SUB(NOW(), INTERVAL 45 day))
        ";
        $this->db->query($deleteAvails);

        $optimizeAvails = "
            optimize table availabilitys
        ";
        $this->db->query($optimizeAvails);
    }

    public function dayOfWeekMap($dayOfWeek)
    {
        $dayOfWeekMap = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday'
        ];

        return $dayOfWeekMap[$dayOfWeek];
    }

}
