<?php
require_once '../bootstrap.php';

use \Slim;
use \SlimProject;
use \MeekroDB;
use \dChallenge\DivvyDB as DivvyDB;

$app = new Slim\Slim([
    'view'            => new Slim\Views\Twig,
    'templates.path'  => '../templates',
]);

// service locators

$app->container->singleton('cache', function() {
    if ($GLOBALS['environment'] == 'production') {
        return new SlimProject\Cache(new SlimProject\Kv\Redis);
    }
    return new SlimProject\NoCache;
});

$app->container->singleton('db', function() {
    return new MeekroDB;
});

// distribute page template
$app->get('/', function() use ($app) {
    $app->render('main.html');
});

// get stations data from json api (for map)
$app->get('/station', function() use ($app) {
    if (($stations = $app->cache->load('stations')) === false) {
        $stations = (new DivvyDB($app->db))->getStationsData();
        $app->cache->save('stations', $stations, 3600);
    }
    echo json_encode($stations);
});

// get station report data from json api (for popup)
$app->get('/station/:id', function($id) use ($app) {
    if (($stationIds = $app->cache->load('stationIds')) === false) {
        $stationIds = (new DivvyDB($app->db))->getStationIds();
        $app->cache->save('stationIds', $stationIds, 14400);
    }
    if (in_array($id, $stationIds)) {   // check if the station id is valid
        $report = new \stdClass;
        if (($timeline = $app->cache->load('timeline_'.$id)) === false) {
            $timeline = (new DivvyDB($app->db))->get72HourTimeline($id);
            $app->cache->save('timeline_'.$id, $timeline, 1200);
        }
        $report->timeline = $timeline;
        if (($graph = $app->cache->load('graph_'.$id)) === false) {
            $graph = (new DivvyDB($app->db))->getDayAveragesGraph($id);
            $app->cache->save('graph_'.$id, $graph, 14400);
        }
        $report->graph = $graph;
        if (($averages = $app->cache->load('averages_'.$id)) === false) {
            $averages = (new DivvyDB($app->db))->getWeeklyAverages($id);
            $app->cache->save('averages_'.$id, $averages, 14400);
        }
        $report->averages = $averages;

        echo json_encode($report);
    } else {
        $app->notFound();
    }
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
