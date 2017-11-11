<?php

require_once '../bootstrap.php';


// setup application and singleton resources
$app = new Slim\Slim([
    'view'            => new Slim\Views\Twig,
    'templates.path'  => '../templates',
]);

$app->container->singleton('cache', function() {
    // return new Kaavii\NoCache;
    return new Kaavii\Cache(\Kaavii\Redis::connect());
});

$app->container->singleton('divvy', function() {
    return new DivvyStat\DB(new MeekroDB);   // config in bootstrap.php
});


// distribute page template
$app->get('/', function() use ($app) {
    $app->render('main.html');
});


// get stations data from json api (for map)
$app->get('/stations', function() use ($app) {
    if (($stations = $app->cache->load('stations')) === false) {
        $stations = $app->divvy->getStationsData();
        $app->cache->save('stations', $stations, 900);
    }
    echo json_encode($stations);
});


// get station report data from json api (for popup)
$app->get('/stations/:landmark', function($landmark) use ($app) {
    if (($landmarks = $app->cache->load('landmarks')) === false) {
        $landmarks = $app->divvy->getLandmarks();
        $app->cache->save('landmarks', $landmarks, 86400);
    }
    if (in_array($landmark, $landmarks)) {   // check if the station landmark is vallandmark
        $report = new \stdClass;
        if (($timeline = $app->cache->load('timeline_'.$landmark)) === false) {
            $timeline = $app->divvy->get72HourStationLine($landmark);
            $app->cache->save('timeline_'.$landmark, $timeline, 600);
        }
        $report->timeline = $timeline;
        if (($graph = $app->cache->load('graph_'.$landmark)) === false) {
            $graph = $app->divvy->getRecentUsageBar($landmark);
            $app->cache->save('graph_'.$landmark, $graph, 86400);
        }
        $report->graph = $graph;
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
