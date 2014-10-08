<?php
require_once 'bootstrap.php';

use \Commando;
use \MeekroDB;
use \dvMap\Tasks;
use \dvMap\DB as DivvyDB;

$app = new Commando\Command();

// command options : prime_cache, daily_cache, update_stations, record_data
// XXX you can add a must() w/ closure to validate the option
$app->option()
    ->require()
    ->describedAs('task to run : prime_cache, record_data, daily_cache, update_stations');

$db = new DivvyDB(new MeekroDB);
switch ($app[0]) {
    case 'prime_cache':
        Tasks::primeCache();
        break;
    case 'record_data':
        Tasks::recordData($db);
        break;
    case 'daily_cache':
        Tasks::dailyCache($db);
        break;
    case 'update_stations':
        Tasks::updateStations($db);
        break;
}
