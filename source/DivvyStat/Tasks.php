<?php
namespace DivvyStat;

class Tasks
{
    public static function updateStations(DB $db)
    {
        $api = new Api;

        foreach ($api->getLiveStationData() as $stationData) {
            if ($stationData->testStation === false) {
                $db->insertUpdateStation(
                    $stationData->landMark,
                    $stationData->stationName,
                    $stationData->latitude,
                    $stationData->longitude
                );
            }
        }
    }

    public static function recordData(DB $db)
    {
        $api = new Api;

        foreach ($api->getLiveStationData() as $station) {
            if (!isset($timestamp)) {
                $timestamp =
                    \DateTime::createFromFormat('Y-m-d H:i:s', $station->timestamp)
                        ->format('Y-m-d H:i:s');
            }

            $landmark = $station->landMark;
            $totalDocks = $station->totalDocks;
            $availableBikes = $station->availableBikes;

            $db->insertAvailability(
                $landmark,
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

        $key = 'stations';
        $cache->delete($key);

        $stations = $db->getStationsData();
        $cache->save($key, $stations, 600);
    }

    public static function dailyCache(DB $divvy)
    {
        $url = 'http://divvystat.us/stations';
        $cache = new \Kaavii\Cache(\Kaavii\Redis::connect());

        $stations = json_decode(\Requests::get($url)->body);
        foreach ($stations as $station) {
            $key = 'graph_' . $station->landmark;
            $cache->delete($key);

            $graph = $divvy->getRecentUsageBar($station->landmark);
            $cache->save($key, $graph, 86400);
        }
    }

    public static function pruneData(DB $db)
    {
        $db->pruneData();
    }

}
