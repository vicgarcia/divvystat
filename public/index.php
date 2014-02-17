<?php
require_once '../bootstrap.php';

use \Slim;
use \SlimProject;
use \PDO;

// instantiate the app and view
$app = new Slim\Slim([
    'view'            => new Slim\Views\Twig,
    'templates.path'  => '../templates',
    'cookies.encrypt' => true,
]);

// setup cache service
$app->container->singleton('cache', function() {
    //if ($GLOBALS['environment'] == 'production')
    //    return new SlimProject\Cache(new SlimProject\Kv);
    return new SlimProject\NoCache;
});

// setup db service
$app->container->singleton('db', function() {
    $config = require 'configure/pdo.php';
    return new PDO($config['dest'], $config['user'], $config['pass']);
});

// a basic route
$app->get('/', function() use ($app) {
    // add decision about desktop vs mobile here
    $app->render('desktop.html');
});

$app->get('/station(/:id)', function($id = null) use ($app) {
    $output = array();
    if (empty($id)) {   // get all stations here
        if (($output = $app->cache->load('stations')) === false) {
            $sql = "select * from station_view";
            $output = $app->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
            //var_dump($output); exit; // xxx testing output from query
            $app->cache->save('stations', $output, 3600);
        }
    } else {            // get station data by id
        if (($stationIds = $app->cache->load('stationIds')) === false) {
            $sql = "select station_id from stations";
            $stationIds = $app->db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
            $app->cache->save('stationIds', $stationIds, 3600);
        }
        // proceed if the provided id is a valid station id
        if (in_array($id, $stationIds)) {
            $report = new \stdClass;

            $timeline = array();
            if (($timeline = $app->cache->load('station'.$id)) === false) {
                $sql = "select * from timeline_view where id = ?";
                $stmt = $app->db->prepare($sql);
                $stmt->execute([$id]);
                $timeline = array();
                foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $key => $point) {
                    if ($key % 3 == 0)
                        $timeline[] = $point;
                }
                $app->cache->save('station'.$id, $timeline, 3600);
            }
            $report->timeline = $timeline;

            $graph = array();
            // XXX replace this with mechanics for getting graph data
            // XXX temp data for testing
            $graph = [
                ['day' => 'Sunday', 'rents' => 76, 'returns' => 84],
                ['day' => 'Monday', 'rents' => 45, 'returns' => 31],
                ['day' => 'Tueday', 'rents' => 63, 'returns' => 61],
                ['day' => 'Wednesday', 'rents' => 55, 'returns' => 37],
                ['day' => 'Thursday', 'rents' => 61, 'returns' => 93],
                ['day' => 'Friday', 'rents' => 38, 'returns' => 12],
                ['day' => 'Saturday', 'rents' => 71, 'returns' => 44]
            ];
            $report->graph = $graph;

            // output
            $output = $report;
        }
    }

    echo json_encode($output);
});

// redirect not found to the landing page
$app->notFound(function () use ($app) { $app->redirect('/'); });

// return empty json array on error
$app->error(function (\Exception $e) use ($app) { echo json_encode([]); });

// run the app!
$app->run();
