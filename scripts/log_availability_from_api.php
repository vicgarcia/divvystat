<?php
require_once '../bootstrap.php';

use \dChallenge\DivvyApi;
use \PDO;

$config = require 'configure/pdo.php';
$db = new PDO($config->conn, $config->user, $config->pass);

$insertSql = preg_replace('/\s+/', ' ', "
    insert into availabilitys
    set
        station_id = :stationId,
        status_key = :statusKey,
        total_docks = :totalDocks,
        available_bikes = :availableBikes,
        timestamp = :timestamp
");

$stmt = $db->prepare($insertSql);
$stmt->bindParam(':stationId', $stationId);
$stmt->bindParam(':statusKey', $statusKey);
$stmt->bindParam(':totalDocks', $totalDocks);
$stmt->bindParam(':availableBikes', $availableBikes);
$stmt->bindParam(':timestamp', $timestamp);

$api = new DivvyApi;
foreach ($api->getStationData() as $station) {
    $stationId = $station->landMark;
    $statusKey = $station->statusKey;
    $totalDocks = $station->totalDocks;
    $availableBikes = $station->availableBikes;

    $datetime = DateTime::createFromFormat('Y-m-d h:i:s a', $station->timestamp);
    $timestamp = $datetime->format('Y-m-d H:i:s');

    if (!$stmt->execute())
        var_dump($stmt->errorInfo());
}
