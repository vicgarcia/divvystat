<?php
require_once 'bootstrap.php';

use \Requests;

$url = 'http://dvmap.chester250.com/station';

$stations = json_decode(Requests::get($url)->body);
foreach ($stations as $station) {
    Requests::get($url.'/'.$station->id);
}
