<?php

// set include path to project root for easy requires
set_include_path(dirname(__FILE__));

// use composer autoloader
require 'vendor/autoload.php';

// configure Kaavii for Redis
Kaavii\Redis::$config =  require 'config/redis.php';

// configure MeekroDB for MySQL
$meekroConfig = require 'config/mysql.php';
DB::$host = $meekroConfig['host'];
DB::$user = $meekroConfig['user'];
DB::$password = $meekroConfig['pass'];
DB::$dbName = $meekroConfig['base'];
unset($meekroConfig);

