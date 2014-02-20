<?php
require_once '../bootstrap.php';

use \Requests;

$url = 'http://74173.ubiquityhosting.com:8000/station';

$stations = json_decode(Requests::get($url)->body);

foreach ($stations as $station) {
    $z = Requests::get($url.'/'.$station->id);
}
