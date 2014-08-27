<?php
require_once 'bootstrap.php';

use \Requests;
use \SlimProject;

$url = 'http://dvmap.chester250.com/station';
$cache = new SlimProject\Cache(SlimProject\Redis::kv());

$stations = json_decode(Requests::get($url)->body);

foreach ($stations as $station) {
    $key = 'graph_' . $station->id;
    $cache->delete($key);
    $graph = $app->divvy->getRecentUsageGraph($id);
    $app->cache->save($key, $graph, 86400);
}

