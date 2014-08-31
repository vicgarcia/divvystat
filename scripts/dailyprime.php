<?php
require_once 'bootstrap.php';

use \Requests;
use \SlimProject;

$url = 'http://dvmap.chester250.com/stations';
$cache = new SlimProject\Cache(SlimProject\Redis::kv());
$divvy = new DivvyDB(new MeekroDB);   // config in bootstrap.php

$stations = json_decode(Requests::get($url)->body);
foreach ($stations as $station) {
    $key = 'graph_' . $station->id;
    $cache->delete($key);
    $graph = $divvy->getRecentUsageGraph($id);
    $cache->save($key, $graph, 86400);
}

