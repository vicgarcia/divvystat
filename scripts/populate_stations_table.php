<?php
require_once '../bootstrap.php';

use \dChallenge\DivvyApi;
use \PDO;

// setup db
$config = require 'configure/pdo.php';
$db = new PDO($config->dest, $config->user, $config->pass);

$insertSql = preg_replace('/\s+/', ' ', "
    insert ignore into stations
    set
        station_id = :stationId,
        name = :stationName,
        latitude = :stationLatitude,
        longitude = :stationLongitude
");
$stmt = $db->prepare($insertSql);
$stmt->bindParam(':stationId', $stationId);
$stmt->bindParam(':stationName', $stationName);
$stmt->bindParam(':stationLatitude', $stationLatitude);
$stmt->bindParam(':stationLongitude', $stationLongitude);

// insert station data from api
$api = new DivvyApi;
foreach ($api->getStationData() as $stationData) {
    $stationId = $stationData->landMark;
    $stationName = $stationData->stationName;
    $stationLatitude = $stationData->latitude;
    $stationLongitude = $stationData->longitude;
    $stmt->execute();
}

