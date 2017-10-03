<?php

/*
 *  usage   : php divvystat.php <option>
 *  options : prime_cache, daily_cache, update_stations, record_data
 *
 *  prime_cache     : perform regular prime of cache for line charts
 *  record_data     : get data from Divvy API and record in app DB
 *  daily_cache     : perform daily cache of day of week charts
 *  update_stations : update app DB stations table from API
 *
 */

require_once 'bootstrap.php';


$cli = new Commando\Command();

$cli->option()
    ->require()
    ->must(function ($option) {
        $options = [
            'prime_cache', 'record_data', 'daily_cache', 'update_stations', 'prune_data'
        ];
        return in_array($option, $options);
    })
    ->describedAs(
        'cli task : prime_cache, record_data, daily_cache, update_stations, prune_data'
    );

$db = new DivvyStat\DB(new MeekroDB);

switch ($cli[0]) {
    case 'prime_cache':
        DivvyStat\Tasks::primeCache($db);
        break;
    case 'record_data':
        DivvyStat\Tasks::recordData($db);
        break;
    case 'daily_cache':
        DivvyStat\Tasks::dailyCache($db);
        break;
    case 'update_stations':
        DivvyStat\Tasks::updateStations($db);
        break;
    case 'prune_data':
        DivvyStat\Tasks::pruneData($db);
        break;
}
