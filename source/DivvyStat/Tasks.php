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

    public static function pruneData(DB $db)
    {
        $db->pruneData();
    }

    public static function clearCache(Cache $cache)
    {
        $cache->clear();
    }
}
