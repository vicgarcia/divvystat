<?php
namespace dChallenge;

class DivvyDB
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
        $sql = "select * from station_view";
        $stations = $this->db->query($sql);

        return $stations;
    }

    public function get72HourTimeline($stationId, \DateTime $timestamp)
    {
        // XXX use timestamp as 72 hr start point, calc endtime by
        //     subtracting 72 hours, default timestamp to now
        $endtime = $timestamp;

        $sql = "
            select timestamp, available_bikes
            from avaliabilitys
            where station_id = %i and timestamp > %t
            order by timestamp desc
            ";
        $rows = $this->db->query($sql, $stationId, $endtime);
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
