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

    public function getLandmarks()
    {
        $sql = "select landmark from stations";
        $landmarks = $this->db->queryOneColumn('landmark', $sql);

        return $landmarks;
    }

    public function getStationsData()
    {
        $sql = "
            select
                s.landmark as 'landmark',
                s.name as 'name',
                s.latitude as 'lat',
                s.longitude as 'lng',
                ( select available_bikes from availabilitys
                  where landmark = s.landmark
                  order by id desc limit 1 ) as 'bikes',
                ( select total_docks from availabilitys
                  where landmark = s.landmark
                  order by id desc limit 1 ) as 'docks'
            from stations s
            ";
        $stations = $this->db->query($sql);

        return $stations;
    }

    public function get72HourStationLine($landmark, \DateTime $end = null)
    {
        // default endtime to now if not explicitly provided
        if ($end == null)
            $end = new \DateTime("now");

        // subtract 72 hours to get the start time
        $start = clone $end;
        $start->sub(new \DateInterval("PT72H"));

        // query for points in interval
        $sql = "
            select timestamp, available_bikes as 'bikes'
            from availabilitys
            where landmark = %i
              and timestamp between %t and %t
            order by timestamp desc
            ";
        $rows = $this->db->query($sql, $landmark, $start, $end);

        // collect points for display on the timeline
        $timeline = [];

        // add the first point to the timeline
        $timeline[] = $rows[0];

        // add intermediate points to the timeline
        $prev = null;
        for ($i = 1 ; $i < (count($rows) - 1) ; $i++) {
            // add if the # of bikes has changed since previous datapoint
            if ($rows[$i]['bikes'] != $prev) {
                $timeline[] = $rows[$i];
                $prev = $rows[$i]['bikes'];
            }
        }

        // add the last point to the timeline
        $timeline[] = $rows[count($rows) - 1];

        return $timeline;
    }

    public function getRecentUsageBar($landmark)
    {
        $rawDataSql = "
            select
              date_format(a.timestamp, '%j') as 'day',
              date_format(a.timestamp,'%w') as 'day_of_week',
              a.timestamp,
              a.available_bikes
            from availabilitys a
            where a.landmark = '%i'
              and DATE(a.timestamp) between DATE(DATE_SUB(NOW(), INTERVAL 31 day))
                                        and DATE(DATE_SUB(NOW(), INTERVAL  1 day))
            order by a.timestamp asc
            ";
        $rows = $this->db->query($rawDataSql, $landmark);

        // populate initial day of week containers
        $days = [];
        $counts = [];
        foreach (range(0, 6) as $dayOfWeek) {
            $days[$dayOfWeek] = [];
            $counts[$dayOfWeek] = 0;
        }

        // parse usage (changes in count) to day of week and track dates (for avg)
        if (count($rows) > 0) {
            $previous = $rows[0]['available_bikes'];
            foreach ($rows as $row) {
                if ($row['available_bikes'] < $previous) {
                    $days[$row['day_of_week']][] = $row['day'];
                    $counts[$row['day_of_week']] += ($previous - $row['available_bikes']);
                }
                $previous = $row['available_bikes'];
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

    public function insertAvailability($landmark, $status, $docks, $bikes, $timestamp)
    {
        return $this->db->insert('availabilitys', [
            'landmark'        => $landmark,
            'status_key'      => $status,
            'total_docks'     => $docks,
            'available_bikes' => $bikes,
            'timestamp'       => $timestamp
        ]);
    }

    public function insertUpdateStation($landmark, $name, $lat, $lng)
    {
        return $this->db->insertUpdate('stations', [
            'landmark'   => $landmark,
            'name'       => $name,
            'latitude'   => $lat,
            'longitude'  => $lng
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
