<?php
namespace DivvyStat;

use \Requests;

class Api
{
    const URL = 'http://divvybikes.com/stations/json';

    public function getLiveStationData()
    {
        $options = [ 'useragent' => 'DivvyStat collector / divvystat.us' ];
        $apiData = json_decode(Requests::get(self::URL, [], $options)->body);

        $timestamp = $apiData->executionTime;

        $results = array();
        foreach ($apiData->stationBeanList as $stationData) {
            $stationData->timestamp = $timestamp;
            $results[$stationData->landMark] = $stationData;
        }

        return $results;
    }
}
