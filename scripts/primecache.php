<?php
require_once 'bootstrap.php';

use \Requests;
use \SlimProject;

$url = 'http://dvmap.chester250.com/station';
$cache = new SlimProject\Cache(SlimProject\Redis::kv());

// delete stations cache, make request to reprime and get ids to loop thru
$cache->delete('stations');
$stations = json_decode(Requests::get($url)->body);

foreach ($stations as $station) {
    // delete timeline from cache and reprime
    $cache->delete('timeline_'.$station->id);
    Requests::get($url.'/'.$station->id, [], ['timeout' => '90']);
}
