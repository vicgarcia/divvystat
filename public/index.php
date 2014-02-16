<?php
require_once '../bootstrap.php';

use \Slim;
use \SlimProject;

// instantiate the app and view
$app = new Slim\Slim([
    'view'            => new Slim\Views\Twig,
    'templates.path'  => '../templates',
    'cookies.encrypt' => true,
]);

// a basic route
$app->get('/', function() use ($app) {
    // add decision about desktop vs mobile here
    $app->render('main.html');
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
