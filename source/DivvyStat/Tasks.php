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

    public static function statusCheck(DB $db, \Mailgun\Mailgun $mg)
    {
        $current = new \DateTime();
        $latest = \DateTime::createFromFormat('Y-m-d H:i:s', $db->getLatestUpdate());

        $seconds = $current->getTimestamp() - $latest->getTimestamp();
        if ($seconds > 3600) {
            $mg->messages()->send('divvystat.us', [
                'from'    => 'system@divvystat.us',
                'to'      => 'vic.garcia@outlook.com',
                'subject' => 'DivvyStat Status Check Failed',
                'text'    => 'DivvyStat stats are not being updated.',
            ]);
        }
    }

}
