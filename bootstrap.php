<?php

// set include path to project root for easy requires
set_include_path(dirname(__FILE__));

// use composer autoloader
require 'vendor/autoload.php';

// set run environment from server port, use global so it's useful outside of slim
$GLOBALS['environment'] = 'development';
if (isset($_SERVER['SERVER_PORT']) and $_SERVER['SERVER_PORT'] == '80') {
    $GLOBALS['environment'] = 'production';
}

// set run environment for cli
if (getcwd() == '/opt/divvystat') {
    $GLOBALS['environment'] = 'production';
}

// configure Kv for Redis
\SlimProject\Redis::$config =  require 'config/redis.php';

// configure DB for MeekroDB
$meekroConfig = require 'config/mysql.php';
\DB::$host = $meekroConfig['host'];
\DB::$user = $meekroConfig['user'];
\DB::$password = $meekroConfig['pass'];
\DB::$dbName = $meekroConfig['base'];
unset($meekroConfig);
