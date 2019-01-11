<?php

/*
 *  php divvystat.php <option>
 *
 *  options ---
 *  record_data : get data from Divvy API and record in app DB
 *  update_stations : update app DB stations table from API
 *  prune_data : delete data from database older than 45 days
 *  status_check : check that stats from divvy are being collected
 *
 */

require_once 'bootstrap.php';

$db = new DivvyStat\DB(new MeekroDB);

$key = require 'config/mailgun.php';
$mg = Mailgun\Mailgun::create($key);

$cli = new Commando\Command();
$cli->option()
    ->require()
    ->must(function ($option) {
        $options = [ 'record_data', 'update_stations', 'prune_data', 'status_check' ];
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
    case 'status_check':
        DivvyStat\Tasks::statusCheck($db, $mg);
        break;
}