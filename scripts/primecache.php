<?php
require_once 'bootstrap.php';

use \Requests;
use \SlimProject;

$baseUrl = 'http://divvystat.us';
$cache = new SlimProject\Cache(SlimProject\Redis::kv());

// these items are cached for 10 minutes in the app itself
// this script should be run every 5 minutes, basically to 'override' that 10 min setting

// reprime the /stations endpoint cache, which handles the station list w/ available bike counts
$cache->delete('stations');
$stations = json_decode(Requests::get($baseUrl.'/stations')->body);

// XXX in the past we've used the stations response above to iterate over the stations
//     individually and re-prime each actual stations cached value for it's timeline graph
//     this proved somewhat excessive for the low traffic we get, so maybe in the future...

// reprime the /outages endpoint cache for the line graph, which needs regular updating
$cache->delete('outages_line');
$outages = json_decode(Requests::get($baseUrl.'/outages')->body);

