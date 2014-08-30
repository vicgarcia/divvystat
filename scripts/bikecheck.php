<?php
require_once 'bootstrap.php';

use \dChallenge;
use \PDO;

// setup db
$config = require 'config/pdo.php';
$db = new PDO($config->conn, $config->user, $config->pass);

// statement to insert availability row
$insertAvailabilitysSql = preg_replace('/\s+/', ' ', "
    insert into availabilitys
    set
        station_id = :stationId,
        status_key = :statusKey,
        total_docks = :totalDocks,
        available_bikes = :availableBikes,
        timestamp = :timestamp
");
$insertAvailabilitys = $db->prepare($insertAvailabilitysSql);
$insertAvailabilitys->bindParam(':stationId', $stationId);
$insertAvailabilitys->bindParam(':statusKey', $statusKey);
$insertAvailabilitys->bindParam(':totalDocks', $totalDocks);
$insertAvailabilitys->bindParam(':availableBikes', $availableBikes);
$insertAvailabilitys->bindParam(':timestamp', $availabilityTimestamp);

// statement to insert defunct row
$insertDefunctSql = preg_replace('/\s+/', ' ', "
    insert into defuncts
    set
        station_count =  :defunctCount,
        detail = :defunctDetail,
        timestamp = :timestamp
");
$insertDefunct = $db->prepare($insertDefunctSql);
$insertDefunct->bindParam(':defunctCount', $defunctCount);
$insertDefunct->bindParam(':defunctDetail', $defunctDetail);
$insertDefunct->bindParam(':timestamp', $defunctTimestamp);


$defunctStations = [
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
        $defunctStations['empty'][] = $stationId;

    if ($availableBikes == $totalDocks)
        $defunctStations['full'][] = $stationId;

    if ($station->statusValue != 'In Service')
        $defunctStations['broken'][] = $stationId;
}

$defunctTimestamp = $availabilityTimestamp;
$defunctCount =
    count($defunctStations['empty']) +
    count($defunctStations['full']) +
    count($defunctStations['broken']);
$defunctDetail = json_encode($defunctStations);

if (!$insertDefunct->execute())
    var_dump($insertDefunct->errorInfo());

