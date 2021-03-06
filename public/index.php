<?php

require_once '../bootstrap.php';

// setup application, cache, database
$app = new Slim\Slim([
    'view'            => new Slim\Views\Twig,
    'templates.path'  => '../templates',
]);
$app->container->singleton('cache', function() {
    return new DivvyStat\Cache();
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
$app->get('/stations/:stationId', function($stationId) use ($app) {
    if (($stationIds = $app->cache->load('stationIds')) === false) {
        $stationIds = $app->db->getStationIds();
        $app->cache->save('stationIds', $stationIds, 86400);
    }
    if (in_array($stationId, $stationIds)) {
        $report = new \stdClass;
        if (($capacity = $app->cache->load('capacity_'.$stationId)) === false) {
            $capacity = $app->db->getStationCapacity($stationId);
            $app->cache->save('capacity:'.$stationId, $capacity, 300);
        }
        $report->capacity = $capacity;
        if (($timeline = $app->cache->load('timeline_'.$stationId)) === false) {
            $timeline = $app->db->getStationTimeline($stationId);
            $app->cache->save('timeline:'.$stationId, $timeline, 300);
        }
        $report->timeline = $timeline;
        if (($graph = $app->cache->load('graph_'.$stationId)) === false) {
            $graph = $app->db->getStationGraph($stationId);
            $app->cache->save('graph:'.$stationId, $graph, 86400);
        }
        $report->graph = $graph;
        echo json_encode($report);
    } else {
        $app->notFound();
    }
});

// status endpoint, returns 500 when data collection is out of date
$app->get('/status', function() use ($app) {
    $latest_update = \DateTime::createFromFormat('Y-m-d H:i:s', $app->db->getLatestUpdate());
    $latest_update_age_in_seconds = (new \DateTime)->getTimestamp() - $latest_update->getTimestamp();
    if ($latest_update_age_in_seconds < 900) {
        $app->response->setStatus(200);
        echo json_encode(['status' => 'ok']);
    } else {
        $app->response->setStatus(500);
        echo json_encode(['status' => 'error']);
    }
});

// return error json array with 404
$app->notFound(function () use ($app) {
    $app->response->setStatus(404);
    echo json_encode(['status' => 'error']);
});

// return error json array on error
$app->error(function (\Exception $e) use ($app) {
    $app->response->setStatus(500);
    echo json_encode(['status' => 'error']);
});

// run the app
$app->run();
