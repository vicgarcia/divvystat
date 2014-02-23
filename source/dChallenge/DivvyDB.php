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

    public function get72HourTimeline($stationId)
    {
        $prev = null;
        $timeline = array();

        $sql = "select * from timeline_view where id = %i";
        foreach ($this->db->query($sql, $stationId) as $row) {
            if ($row['bikes'] != $prev) {
                $timeline[] = $row;
                $prev = $row['bikes'];
            }
        }

        return $timeline;
    }

    public function getDayAveragesGraph($stationId)
    {
        $graph = array();

        foreach (range(0, 6) as $day) {
            $graph[$day]['day'] = $this->dayOfWeekMap()[$day];
        }

        $rentSql = "select * from trips_rents_view where station_id = %i";
        foreach ($this->db->query($rentSql, $stationId) as $row) {
            $graph[$row['day']]['rents'] = $row['rents'];
        }

        $returnSql = "select * from trips_returns_view where station_id = %i";
        foreach ($this->db->query($returnSql, $stationId) as $row) {
            $graph[$row['day']]['returns'] = $row['returns'];
        }

        return $graph;
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
