<?php
namespace DivvyStat;

class Api
{
    const URL = 'https://layer.bicyclesharing.net/map/v1/chi/map-inventory';

    public function getLiveStationData()
    {
        $options = ['useragent' => 'DivvyStat collector / divvystat.us'];
        $apiData = json_decode(\Requests::get(self::URL, [], $options)->body);

        $results = [];
        foreach ($apiData->features as $data) {

            // parse the api results to a 'station' object
            $station = new \stdClass;

            // parse the id (int) and uuid (string)
            $station->id = $data->properties->station->id;
            $station->uuid = $data->properties->station->id_public;

            // name is that display name of the station
            $station->name = $data->properties->station->name;

            // parse unix timestamp to native datetime
            $station->timestamp = (new \DateTime('now'))
                ->setTimezone(new \DateTimeZone('America/Chicago'));

            // parse latitude and longitude
            $station->latitude = $data->geometry->coordinates[1];
            $station->longitude = $data->geometry->coordinates[0];

            // parse available bikes and total docks at station
            $station->availableBikes = $data->properties->station->bikes_available;
            $station->totalDocks = $data->properties->station->capacity;

            // parse if the station is currently active
            $station->active = $data->properties->station->renting;

            // prevent duplicates by indexing by unique station terminal (id)
            $results[$station->id] = $station;

        }

        return $results;
    }

}
