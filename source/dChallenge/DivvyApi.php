<?php
namespace dChallenge;

use \Requests;

class DivvyApi
{
    const URL = 'http://divvybikes.com/stations/json';

    public function getStationData()
    {
        $apiData = json_decode(Requests::get(self::URL)->body);

        $timestamp = $apiData->executionTime;

        $results = array();
        foreach ($apiData->stationBeanList as $stationData) {
            $stationData->timestamp = $timestamp;
            $results[$stationData->landMark] = $stationData;
        }

        return $results;
    }
}
