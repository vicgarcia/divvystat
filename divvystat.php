<?php

/*
 *  php divvystat.php <option>
 *
 *  options ---
 *  record_data : get data from Divvy API and record in app DB
 *  update_stations : update app DB stations table from API
 *  prune_data : delete data from database older than 45 days
 *
 */

require_once 'bootstrap.php';


$db = new DivvyStat\DB(new MeekroDB);

$cli = new Commando\Command();

$cli->option()
    ->require()
    ->must(function ($option) {
        $options = [ 'record_data', 'update_stations', 'prune_data' ];
        return in_array($option, $options);
    })
    ->describedAs('provide a command line option');

switch ($cli[0]) {
    case 'record_data':
        DivvyStat\Tasks::recordData($db);
        break;
    case 'update_stations':
        DivvyStat\Tasks::updateStations($db);
        break;
    case 'prune_data':
        DivvyStat\Tasks::pruneData($db);
        break;
}
