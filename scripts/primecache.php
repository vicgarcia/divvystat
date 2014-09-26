<?php
require_once 'bootstrap.php';

use \Requests;
use \SlimProject;

$url = 'http://divvystat.us/stations';
$cache = new SlimProject\Cache(SlimProject\Redis::kv());

// delete stations cache, make request to reprime and get ids to loop thru
$cache->delete('stations');     // cached for 10 by app, reprime every 5
$stations = json_decode(Requests::get($url)->body);

