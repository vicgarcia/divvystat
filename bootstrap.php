<?php

// set include path to project root for easy requires
set_include_path(dirname(__FILE__));

// use composer autoloader
require 'vendor/autoload.php';

// config for redis
$redis =  require 'config/redis.php';
DivvyStat\Cache::$host = $redis['host'];
DivvyStat\Cache::$port = $redis['port'];
DivvyStat\Cache::$database = $redis['base'];

// configure for mysql
$mysql = require 'config/mysql.php';
DB::$host = $mysql['host'];
DB::$user = $mysql['user'];
DB::$password = $mysql['pass'];
DB::$dbName = $mysql['base'];
