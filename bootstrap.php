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

// configure Kv for Redis
\SlimProject\Kv\Redis::$prefix =  require 'config/redis.php';
\SlimProject\Kv\Redis::$prefix = 'dvMap:';
