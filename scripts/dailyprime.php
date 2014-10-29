<?php
require_once 'bootstrap.php';

use \Requests;
use \SlimProject;
use \dChallenge\DivvyDB as DivvyDB;

$cache = new SlimProject\Cache(SlimProject\Redis::kv());
$divvy = new DivvyDB(new MeekroDB);   // config in bootstrap.php

// reprime the day of week bar graph for each station
$stations = json_decode(Requests::get('http://divvystat.us/stations')->body);
foreach ($stations as $station) {
    $key = 'graph_' . $station->id;
    $cache->delete($key);
    $graph = $divvy->getRecentUsageBar($station->id);
    $cache->save($key, $graph, 86400);
}

// reprime the day of week bar graph for outages
$key = 'outages_bar';
$cache->delete($key);
$graph = $divvy->getRecentOutagesBar();
$cache->save($key, $graph, 86400);
