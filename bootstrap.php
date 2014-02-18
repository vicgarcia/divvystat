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

// configure cookie options
//\SlimProject\SampleUser::$config = require 'config/cookies.php';

// redis config (examples using either Redis or Predis client)
\SlimProject\Kv\Redis::$config = require 'configure/redis.php';
/*
\SlimProject\Kv\Predis::$prefix = 'slimproject:';
*/

// pdo database config
$GLOBALS['mysql'] = require 'configure/mysql.php';

