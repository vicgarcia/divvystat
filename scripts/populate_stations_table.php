<?php
require_once '../bootstrap.php';

use \dChallenge\DivvyApi;
use \PDO;

$insertSql = preg_replace('/\s+/', ' ', "
    insert ignore into stations
    set
        station_id = :stationId,
        name = :stationName,
        latitude = :stationLatitude,
        longitude = :stationLongitude
");

$config = require 'configure/pdo.php';

$db = new PDO($config->dest, $config->user, $config->pass);

$stmt = $db->prepare($insertSql);
$stmt->bindParam(':stationId', $stationId);
$stmt->bindParam(':stationName', $stationName);
$stmt->bindParam(':stationLatitude', $stationLatitude);
$stmt->bindParam(':stationLongitude', $stationLongitude);

$api = new DivvyApi;
foreach ($api->getStationData() as $stationData) {
    $stationId = $stationData->landMark;
    $stationName = $stationData->stationName;
    $stationLatitude = $stationData->latitude;
    $stationLongitude = $stationData->longitude;
    //var_dump($stationData); exit; // xxx reorder wrt execute()
    $stmt->execute();
}

