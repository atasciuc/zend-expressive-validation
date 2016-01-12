<?php
// Here you can initialize variables that will be available to your tests
define('FUNCTIONAL_ROOT_PATH', __DIR__);
define('APP_VERSION', '0.0.1');
define('APP_ROOT_PATH', __DIR__ . '/../..');
define('DATA_ROOT', APP_ROOT_PATH . '/data');
// Load the environment variables
require __DIR__ . '/../../vendor/autoload.php';
$dotenv = new \Dotenv\Dotenv(APP_ROOT_PATH . '/config/');
$dotenv->load();