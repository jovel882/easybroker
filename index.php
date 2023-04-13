<?php

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use Jovel\Easybroker\GetProperties;

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$getProperties = new GetProperties(new Client(), 'https://api.stagingeb.com/v1/properties?page=1&limit=50');
$getProperties->loadProperties();
