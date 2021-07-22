<?php

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

ini_set('date.timezone', 'Asia/Manila');
date_default_timezone_set('Asia/Manila');

use App\Core\App;
use App\Core\Database\QueryBuilder;
use App\Core\Database\Connection;

$config_file = 'config.php';
if (!file_exists($config_file)) {
    die("The [config.php] not found.");
}

require $config_file;

App::bind('config', require __DIR__ . '/EnvConfig.php');

App::bind(
    'base_url',
    (!empty(App::get('config')['app']['base_url']))
        ? '/' . App::get('config')['app']['base_url']
        : App::get('config')['app']['base_url']
);

if (isset(App::get('config')['database']['name'])) {
    App::bind('database', new QueryBuilder(
        Connection::make(App::get('config')['database'])
    ));
}
