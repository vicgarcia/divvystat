<?php
/*
 *  usage   : php dvmap.php <option>
 *  options : prime_cache, daily_cache, update_stations, record_data
 *
 *  prime_cache     : perform regular prime of cache for line charts
 *  record_data     : get data from Divvy API and record in app DB
 *  daily_cache     : perform daily cache of day of week charts
 *  update_stations : update app DB stations table from API
 *
 */

require_once 'bootstrap.php';

use \Commando;
use \MeekroDB;
use \dvMap\Tasks;
use \dvMap\DB as DivvyDB;


$cli = new Commando\Command();

$cli->option()
    ->require()
    ->must(function ($option) {
        $options = array(
            'prime_cache', 'record_data', 'daily_cache', 'update_stations'
        );
        return in_array($option, $options);
    })
    ->describedAs(
        'cli task : prime_cache, record_data, daily_cache, update_stations'
    );

$db = new DivvyDB(new MeekroDB);

switch ($cli[0]) {
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
