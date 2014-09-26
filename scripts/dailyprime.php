<?php
require_once 'bootstrap.php';

use \Requests;
use \SlimProject;
use \dChallenge\DivvyDB as DivvyDB;

$url = 'http://divvystat.us/stations';
$cache = new SlimProject\Cache(SlimProject\Redis::kv());
$divvy = new DivvyDB(new MeekroDB);   // config in bootstrap.php

$stations = json_decode(Requests::get($url)->body);
foreach ($stations as $station) {
    $key = 'graph_' . $station->id;
    $cache->delete($key);
    $graph = $divvy->getRecentUsageGraph($station->id);
    $cache->save($key, $graph, 86400);
}

