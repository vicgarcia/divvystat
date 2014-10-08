<?php
namespace dvMap;

use \Requests;
use \SlimProject;

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

        $timestamp =
            DateTime::createFromFormat('Y-m-d h:i:s a', $station->timestamp)
                    ->format('Y-m-d H:i:s');

        foreach ($api->getLiveStationData() as $station) {
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

            if ($availableBikes == 0)
                $outageStations['empty'][] = $stationId;

            if ($availableBikes == $totalDocks)
                $outageStations['full'][] = $stationId;

            if ($station->statusKey != 1)
                $outageStations['broken'][] = $stationId;
        }

        $outageCount =
            count($outageStations['empty']) +
            count($outageStations['full']) +
            count($outageStations['broken']);
        $outageDetail = json_encode($outageStations);

        $db->insertOutage($outageCount, $outageDetail, $timestamp);
    }

    public static function primeCache()
    {
        $url = 'http://divvystat.us/stations';
        $cache = new SlimProject\Cache(SlimProject\Redis::kv());

        // delete stations cache, make request to reprime and get ids to loop thru
        $cache->delete('stations');     // cached for 10 by app, reprime every 5
        $stations = json_decode(Requests::get($url)->body);
    }

    public static function dailyCache(DB $db)
    {
        $url = 'http://divvystat.us/stations';
        $cache = new SlimProject\Cache(SlimProject\Redis::kv());

        $stations = json_decode(Requests::get($url)->body);
        foreach ($stations as $station) {
            $key = 'graph_' . $station->id;
            $cache->delete($key);
            $graph = $db->getRecentUsageGraph($station->id);
            $cache->save($key, $graph, 86400);
        }
    }
}
