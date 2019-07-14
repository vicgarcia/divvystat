<?php
namespace DivvyStat;

class Tasks
{

    public static function updateStations(DB $db)
    {
        $api = new Api;

        foreach ($api->getLiveStationData() as $stationData) {
            $db->insertUpdateStation(
                $stationData->terminal,
                $stationData->name,
                $stationData->latitude,
                $stationData->longitude
            );
        }
    }

    public static function recordData(DB $db)
    {
        $api = new Api;

        foreach ($api->getLiveStationData() as $station) {
            $db->insertAvailability(
                $station->terminal,
                $station->totalDocks,
                $station->availableBikes,
                $station->timestamp
            );
        }
    }

    public static function pruneData(DB $db)
    {
        $db->pruneData();
    }

    public static function clearCache(Cache $cache)
    {
        $cache->clear();
    }
}
