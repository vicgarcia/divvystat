<?php
namespace DivvyStat;

use \Carbon\Carbon;
use \Requests;

class Tasks
{
    public static function updateStations(DB $db)
    {
        $api = new Api;

        foreach ($api->getLiveStationData() as $stationData) {
            $db->insertUpdateStation(
                $stationData->landMark,
                $stationData->stationName,
                $stationData->latitude,
                $stationData->longitude
            );
        }
    }

    public static function recordData(DB $db)
    {
        $api = new Api;

        foreach ($api->getLiveStationData() as $station) {
            if (!isset($timestamp)) {
                $timestamp =
                    \DateTime::createFromFormat('Y-m-d h:i:s a', $station->timestamp)
                        ->format('Y-m-d H:i:s');
            }

            $stationId = $station->landMark;
            $totalDocks = $station->totalDocks;
            $availableBikes = $station->availableBikes;

            $db->insertAvailability(
                $stationId,
                $station->statusKey,
                $totalDocks,
                $availableBikes,
                $timestamp
            );
        }
    }

    public static function primeCache(DB $db)
    {
        $url = 'http://divvystat.us/';
        $cache = new \Kaavii\Cache(\Kaavii\Redis::connect());

        // delete stations cache, make request to reprime and get ids to loop thru
        $stations = $db->getStationsData();
        $cache->delete('stations');     // cached for 15 by app, reprime every 10
        $cache->save('stations', $stations, 900);
    }

    public static function dailyCache(DB $divvy)
    {
        $url = 'http://divvystat.us/stations';
        $cache = new \Kaavii\Cache(\Kaavii\Redis::connect());

        $stations = json_decode(Requests::get($url)->body);
        foreach ($stations as $station) {
            $key = 'graph_' . $station->id;
            $graph = $divvy->getRecentUsageBar($station->id);
            $cache->delete($key);
            $cache->save($key, $graph, 86400);
        }
    }

    public static function pruneData(DB $db)
    {
        $db->pruneData();
    }

}
