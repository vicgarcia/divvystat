<?php
require_once '../bootstrap.php';

use \Slim;
use \SlimProject;
use \MeekroDB;
use \dChallenge;

// instantiate the app and view
$app = new Slim\Slim([
    'view'            => new Slim\Views\Twig,
    'templates.path'  => '../templates',
]);

// setup cache service
$app->container->singleton('cache', function() {
    if ($GLOBALS['environment'] == 'production') {
        $config = require 'configure/redis.php';
        return new SlimProject\Cache(new SlimProject\Kv\Redis($config));
    }
    return new SlimProject\NoCache;
});

// setup db service
$app->container->singleton('db', function() {
    extract(require 'configure/mysql.php');
    return new MeekroDB($host, $user, $pass, $base);
});

// distribute page template
$app->get('/', function() use ($app) {
    // add decision about desktop vs mobile here
    $app->render('desktop.html');
});

// get stations data from json api (for map)
$app->get('/station', function() use ($app) {
    $stations = array();
    if (($stations = $app->cache->load('stations')) === false) {
        $stations = (new dChallenge\DivvyDB($app->db))->getStationsData();
        $app->cache->save('stations', $stations, 3600);
    }
    echo json_encode($stations);
});

// get station report data from json api (for popup)
$app->get('/station/:id', function($id) use ($app) {
    if (($stationIds = $app->cache->load('stationIds')) === false) {
        $stationIds = (new dChallenge\DivvyDB($app->db))->getStationIds();
        $app->cache->save('stationIds', $stationIds, 14400);
    }
    if (in_array($id, $stationIds)) {   // check if the station id is valid
        $db = new dChallenge\DivvyDB($app->db);
        $report = new \stdClass;

        $timeline = array();
        if (($timeline = $app->cache->load('timeline_'.$id)) === false) {
            $timeline = $db->get72HourTimeline($id);
            $app->cache->save('timeline_'.$id, $timeline, 1200);
        }
        $report->timeline = $timeline;

        $graph = array();
        if (($graph = $app->cache->load('graph_'.$id)) === false) {
            $graph = $db->getDayAveragesGraph($id);
            $app->cache->save('graph_'.$id, $graph, 14400);
        }
        $report->graph = $graph;

        echo json_encode($report);
    } else {
        $app->notFound();
    }
});

// redirect not found to the landing page
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
