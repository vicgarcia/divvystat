<?php

require_once '../bootstrap.php';

// setup application, cache, database
$app = new Slim\Slim([
    'view'            => new Slim\Views\Twig,
    'templates.path'  => '../templates',
]);
$app->container->singleton('cache', function() {
    // return new Kaavii\NoCache;
    return new Kaavii\Cache(\Kaavii\Redis::connect());
});
$app->container->singleton('db', function() {
    return new DivvyStat\DB(new MeekroDB);
});

// distribute page template
$app->get('/', function() use ($app) {
    $app->render('main.html');
});

// endpoint for stations json (used to populate markers to map)
$app->get('/stations', function() use ($app) {
    if (($stations = $app->cache->load('stations')) === false) {
        $stations = $app->db->getStations();
        $app->cache->save('stations', $stations, 86400);
    }
    echo json_encode($stations);
});

// endpoint for per-station json (used to populate charts in popup)
$app->get('/stations/:landmark', function($landmark) use ($app) {
    if (($landmarks = $app->cache->load('landmarks')) === false) {
        $landmarks = $app->db->getLandmarks();
        $app->cache->save('landmarks', $landmarks, 86400);
    }
    if (in_array($landmark, $landmarks)) {
        $report = new \stdClass;
        if (($capacity = $app->cache->load('capacity_'.$landmark)) === false) {
            $capacity = $app->db->getStationCapacity($landmark);
            $app->cache->save('capacity:'.$landmark, $capacity, 300);
        }
        $report->capacity = $capacity;
        if (($timeline = $app->cache->load('timeline_'.$landmark)) === false) {
            $timeline = $app->db->getStationTimeline($landmark);
            $app->cache->save('timeline:'.$landmark, $timeline, 300);
        }
        $report->timeline = $timeline;
        if (($graph = $app->cache->load('graph_'.$landmark)) === false) {
            $graph = $app->db->getStationGraph($landmark);
            $app->cache->save('graph:'.$landmark, $graph, 86400);
        }
        $report->graph = $graph;
        echo json_encode($report);
    } else {
        $app->notFound();
    }
});

// status endpoint, returns 500 when data collection is out of date
$app->get('/status', function() use ($app) {
    $latest = \DateTime::createFromFormat('Y-m-d H:i:s', $app->db->getLatestUpdate());
    $latest_update_age_in_seconds = (new \DateTime)->getTimestamp() - $latest->getTimestamp();
    if ($latest_update_age_in_seconds < 900) {
        $app->response->setStatus(200);
        echo json_encode([ 'status' => 'ok' ]);
    } else {
        $app->response->setStatus(500);
        echo json_encode([ 'status' => 'error' ]);
    }
});

// return error json array with 404
$app->notFound(function () use ($app) {
    $app->response->setStatus(404);
    echo json_encode([ 'status' => 'error' ]);
});

// return error json array on error
$app->error(function (\Exception $e) use ($app) {
    $app->response->setStatus(500);
    echo json_encode([ 'status' => 'error' ]);
});

// run the app
$app->run();