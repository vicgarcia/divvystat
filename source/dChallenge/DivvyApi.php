<?php
namespace dChallenge;

use \Requests;

class DivvyApi
{
    const URL = 'http://divvybikes.com/stations/json';

    public function getStationData()
    {
        $response = Requests::get(self::URL);
        $apiData = json_decode($response->body);

        $timestamp = $apiData->executionTime;

        $results = array();
        foreach ($apiData->stationBeanList as $stationData) {
            $stationData->timestamp = $timestamp;
            $results[$stationData->landMark] = $stationData;
        }

        return $results;
    }

}
