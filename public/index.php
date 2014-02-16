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

// load database config
$app->dbConfig = require 'configure/pdo.php';

// a basic route
$app->get('/', function() use ($app) {
    // add decision about desktop vs mobile here
    $app->render('desktop.html');
});

$app->get('/station(/:id)', function($id = null) use ($app) {
    // connect cache for use
    if ($GLOBALS['environment'] == 'production') {
        //$cache = new SlimProject\Cache(new SlimProject\Kv);
    } else {
        $cache = new SlimProject\NoCache;
    }

    $output = array();
    if (empty($id)) {
        // get all stations here
        $config = $app->dbConfig;
        $db = new PDO($config->dest, $config->user, $config->pass);
        $sql = "select * from stations";
        foreach ($db->query($sql) as $row) {
            $station = new \stdClass();
            $station->station_id = $row['station_id'];
            $station->name = $row['name'];
            $station->latitude = $row['latitude'];
            $station->longitude = $row['longitude'];
            $output[] = $station;
        }
    } else {
        /*
        // have an array of station ids for verification
        if (($stationIds = $cache->load('stationIds')) === false) {
            // get db query
            $query = new DChallenge\Stations(new DChallenge\Db);
            $stationIds = $query->getStationIds();
            $cache->save('stationIds', $stationIds, 3600);
        }
        */

        /*
        // proceed if the provided id is a valid station id
        if (is_int($id) and in_array($id, $stationIds)) {
            // get from divvy api current info
        }
        */
    }

    echo json_encode($output);
});

// redirect not found to the landing page
$app->notFound(function () use ($app) { $app->redirect('/'); });

// return empty json array on error
$app->error(function (\Exception $e) use ($app) { echo json_encode([]); });

// run the app!
$app->run();
