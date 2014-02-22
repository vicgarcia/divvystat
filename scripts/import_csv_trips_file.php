<?php
require_once '../bootstrap.php';

use \PDO;

$config = require 'configure/pdo.php';
$db = new PDO($config['dest'], $config['user'], $config['pass']);

$insertSql = preg_replace('/\s+/', ' ', "
    insert ignore into trips
    set
        trip_id = :tripId,
        depart = :depart,
        arrive = :arrive,
        bike_id = :bikeId,
        duration = :duration,
        origin = :origin,
        destination = :destination,
        user = :user,
        gender = :gender,
        birthyear = :birthyear
");
$stmt = $db->prepare($insertSql);
$stmt->bindParam(':tripId', $tripId);
$stmt->bindParam(':depart', $depart);
$stmt->bindParam(':arrive', $arrive);
$stmt->bindParam(':bikeId', $bikeId);
$stmt->bindParam(':duration', $duration);
$stmt->bindParam(':origin', $origin);
$stmt->bindParam(':destination', $destination);
$stmt->bindParam(':user', $user);
$stmt->bindParam(':gender', $gender);
$stmt->bindParam(':birthyear', $birthyear);

$csvFilename = __DIR__.'trips_data.csv';
if (($file = fopen($csvFilename, "r")) !== false) {
    while (($data = fgetcsv($file, 0, ',', '"')) !== false) {
        // parse csv data
        //var_dump($data); exit; // xxx useful for debugging
        $tripId = $data[0];
        $depart = DateTime::createFromFormat('m/d/Y H:i', $data[1])
            ->format('Y-m-d H:i:s');
        $arrive = DateTime::createFromFormat('m/d/Y H:i', $data[2])
            ->format('Y-m-d H:i:s');
        $bikeId = $data[3];
        $duration = $data[4];
        $origin = $data[5];
        $destination = $data[7];
        $user = $data[9];
        $gender = $data[10];
        $birthyear = $data[11];

        // insert row
        if (!$stmt->execute()) {
            var_dump($stmt->errorInfo());
            exit;
        }
    }
    fclose($file);
}
