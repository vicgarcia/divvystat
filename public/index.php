<?php
require_once '../bootstrap.php';

use \Slim;
use \SlimProject;
use \MeekroDB;
use \DivvyStat\DB as DivvyDB;

$app = new Slim\Slim([
    'view'            => new Slim\Views\Twig,
    'templates.path'  => '../templates',
]);

$app->container->singleton('cache', function() {
    if ($GLOBALS['environment'] != 'production')
        return new SlimProject\NoCache;
    return new SlimProject\Cache(SlimProject\Redis::kv());
});

$app->container->singleton('divvy', function() {
    return new DivvyDB(new MeekroDB);   // config in bootstrap.php
});

// distribute page template
$app->get('/', function() use ($app) {
    $app->render('main.html');
});

// get stations data from json api (for map)
$app->get('/stations', function() use ($app) {
    if (($stations = $app->cache->load('stations')) === false) {
        $stations = $app->divvy->getStationsData();
        $app->cache->save('stations', $stations, 600);
    }
    echo json_encode($stations);
});

// get station report data from json api (for popup)
$app->get('/stations/:id', function($id) use ($app) {
    if (($stationIds = $app->cache->load('stationIds')) === false) {
        $stationIds = $app->divvy->getStationIds();
        $app->cache->save('stationIds', $stationIds, 86400);
    }
    if (in_array($id, $stationIds)) {   // check if the station id is valid
        $report = new \stdClass;
        if (($timeline = $app->cache->load('timeline_'.$id)) === false) {
            $timeline = $app->divvy->get72HourStationLine($id);
            $app->cache->save('timeline_'.$id, $timeline, 600);
        }
        $report->timeline = $timeline;
        if (($graph = $app->cache->load('graph_'.$id)) === false) {
            $graph = $app->divvy->getRecentUsageBar($id);
            $app->cache->save('graph_'.$id, $graph, 86400);
        }
        $report->graph = $graph;
        echo json_encode($report);
    } else {
        $app->notFound();
    }
});

// get outage report data from json api (for below-the-fold charts)
$app->get('/outages', function() use ($app) {
    $report = new \stdClass;
    if (($line = $app->cache->load('outages_line')) === false) {
        $line = $app->divvy->get72HourOutageLine();
        $app->cache->save('outages_line', $line, 600);
    }
    $report->line = $line;
    if (($bar = $app->cache->load('outages_bar')) === false) {
        $bar = $app->divvy->getRecentOutageBar();
        $app->cache->save('outages_bar', $bar, 86400);
    }
    $report->bar = $bar;
    echo json_encode($report);
});

// return empty json array with 404
$app->notFound(function () use ($app) {
    $app->response->setStatus(404);
    echo json_encode([]);
});

// return empty json array on error
$app->error(function (\Exception $e) use ($app) {
    $app->response->setStatus(500);
    echo json_encode([]);
});

// run the app
$app->run();
