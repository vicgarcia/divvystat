<?php
require_once '../bootstrap.php';

use \Slim;
use \SlimProject;
use \PDO;
use \MeekroDB;

// instantiate the app and view
$app = new Slim\Slim([
    'view'            => new Slim\Views\Twig,
    'templates.path'  => '../templates',
    'cookies.encrypt' => true,
]);

// setup cache service
$app->container->singleton('cache', function() {
    //if ($GLOBALS['environment'] == 'production') {
    if (true) {
        return new SlimProject\Cache(new SlimProject\Kv\Redis);
    }
    return new SlimProject\NoCache;
});

// setup db service
$app->container->singleton('db', function() {
    //$config = $GLOBALS['pdo'];
    //return new PDO($config['dest'], $config['user'], $config['pass']);

    $cfg = $GLOBALS['mysql'];
    return new MeekroDB($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['base']);
});

// distribute page template
$app->get('/', function() use ($app) {
    // add decision about desktop vs mobile here
    $app->render('desktop.html');
});

// station api
$app->get('/station(/:id)', function($id = null) use ($app) {
    $output = array();
    if (empty($id)) {                                   // get all stations here
        /*
        if (($output = $app->cache->load('stations')) === false) {
            $sql = "select * from station_view";
            $output = $app->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
            $app->cache->save('stations', $output, 3600);
        }
        */
        $sql = "select * from station_view";
        $output = $app->db->query($sql);
        //var_dump($r); exit;
    } else {                                            // get station data by id
        if (($stationIds = $app->cache->load('stationIds')) === false) {
            $sql = "select station_id from stations";
            $stationIds = $app->db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
            $app->cache->save('stationIds', $stationIds, 3600);
        }
        if (in_array($id, $stationIds)) {               // proceed if valid station_id
            $report = new \stdClass;

            $timeline = array();
            if (($timeline = $app->cache->load('timeline_'.$id)) === false) {
                $sql = "select * from timeline_view where id = ?";
                $stmt = $app->db->prepare($sql);
                $stmt->execute([$id]);
                foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $key => $point) {
                    if ($key % 3 == 0)
                        $timeline[] = $point;
                }
                $app->cache->save('timeline_'.$id, $timeline, 3600);
            }
            $report->timeline = $timeline;

            $graph = array();
            if (($graph = $app->cache->load('graph_'.$id)) === false) {
                $days = array(
                    0 => 'Sunday',
                    1 => 'Monday',
                    2 => 'Tuesday',
                    3 => 'Wednesday',
                    4 => 'Thursday',
                    5 => 'Friday',
                    6 => 'Saturday'
                );
                foreach (range(0, 6) as $day) {
                    $graph[$day]['day'] = $days[$day];
                }

                $rentSql = "select * from trips_rents_view where station_id = ?";
                $rentStmt = $app->db->prepare($rentSql);
                $rentStmt->execute([$id]);
                foreach ($rentStmt->fetchAll(PDO::FETCH_OBJ) as $row) {
                    $graph[$row->day]['rents'] = $row->rents;
                }

                $returnSql = "select * from trips_returns_view where station_id = ?";
                $returnStmt = $app->db->prepare($returnSql);
                $returnStmt->execute([$id]);
                foreach ($returnStmt->fetchAll(PDO::FETCH_OBJ) as $row) {
                    $graph[$row->day]['returns'] = $row->returns;
                }
                $app->cache->save('graph_'.$id, $graph, 3600);
            }
            $report->graph = $graph;

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
