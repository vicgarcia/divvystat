<?php
require_once '../bootstrap.php';

use \Slim;
use \SlimProject;
use \PDO;

// ad-hoc function to fix sql whitespace
function prepSql($string)
{
    return preg_replace('/\s+/', ' ', $string);
}

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
    if (empty($id)) {                           // get all stations here
        if (($output = $app->cache->load('stations')) === false) {
            $sql = prepSql("
                select s.station_id, s.name, s.latitude, s.longitude,
                  ( select available_bikes from availabilitys
                    where station_id = s.station_id
                    order by timestamp desc limit 1 ) as 'bikes',
                  ( select total_docks from availabilitys
                    where station_id = s.station_id
                    order by timestamp desc limit 1 ) as 'docks'
                from stations s
            ");
            $output = $app->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
            //var_dump($output); exit; // xxx testing output from query
            $app->cache->save('stations', $output, 3600);
        }
    } else {                                    // get station data by id
        if (($stationIds = $app->cache->load('stationIds')) === false) {
            $sql = prepSql("select distinct station_id from stations");
            $stationIds = $app->db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
            $app->cache->save('stationIds', $stationIds, 3600);
        }
        // proceed if the provided id is a valid station id
        if (in_array($id, $stationIds)) {
            $report = new \stdClass;
            $report->timeline = array('test', 'data', 'here');
            $report->average = array();

            // get from divvy api current info

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
