<?php
require_once 'bootstrap.php';

use \dChallenge;
use \PDO;

// setup db
$config = require 'config/pdo.php';
$db = new PDO($config->conn, $config->user, $config->pass);

// statement to insert availability row
$insertAvailabilitysSql = "
    insert into availabilitys
    set
        station_id = :stationId,
        status_key = :statusKey,
        total_docks = :totalDocks,
        available_bikes = :availableBikes,
        timestamp = :timestamp
    ";
$insertAvailabilitys = $db->prepare($insertAvailabilitysSql);
$insertAvailabilitys->bindParam(':stationId', $stationId);
$insertAvailabilitys->bindParam(':statusKey', $statusKey);
$insertAvailabilitys->bindParam(':totalDocks', $totalDocks);
$insertAvailabilitys->bindParam(':availableBikes', $availableBikes);
$insertAvailabilitys->bindParam(':timestamp', $availabilityTimestamp);

// statement to insert outage row
$insertOutageSql = "
    insert into outages
    set
        station_count =  :outageCount,
        detail = :outageDetail,
        timestamp = :timestamp
    ";
$insertOutage = $db->prepare($insertOutageSql);
$insertOutage->bindParam(':outageCount', $outageCount);
$insertOutage->bindParam(':outageDetail', $outageDetail);
$insertOutage->bindParam(':timestamp', $outageTimestamp);


$outageStations = [
    'broken' => [],
    'empty'  => [],
    'full'   => []
    ];

$api = new dChallenge\DivvyApi;
foreach ($api->getLiveStationData() as $station) {
    $stationId = $station->landMark;
    $statusKey = $station->statusKey;
    $totalDocks = $station->totalDocks;
    $availableBikes = $station->availableBikes;

    $datetime = DateTime::createFromFormat('Y-m-d h:i:s a', $station->timestamp);
    $availabilityTimestamp = $datetime->format('Y-m-d H:i:s');

    if (!$insertAvailabilitys->execute())
        var_dump($insertAvailabilitys->errorInfo());

    if ($availableBikes == 0)
        $outageStations['empty'][] = $stationId;

    if ($availableBikes == $totalDocks)
        $outageStations['full'][] = $stationId;

    if ($station->statusKey != 1)
        $outageStations['broken'][] = $stationId;
}

$outageTimestamp = $availabilityTimestamp;
$outageCount =
    count($outageStations['empty']) +
    count($outageStations['full']) +
    count($outageStations['broken']);
$outageDetail = json_encode($outageStations);

if (!$insertOutage->execute())
    var_dump($insertOutage->errorInfo());

