<?php

//Config
$settings = [
    'db' => [
        'host' => 'CHANGE ME', /* This line is changeable. */
        'user' => 'adusheck', /* This line is changeable. */
        'pass' => 'CHANGE ME', /* This line is changeable. */
        'name' => 'apcspsign' /* This line is changeable. */
    ],
    'general' => [
        'siteRoot' => (!empty($_SERVER['HTTPS']) ? 'https':'http').'://'.$_SERVER['HTTP_HOST'],
        'recaptchaKey' => 'CHANGE ME',
        'latestClientVersion' => '0.2'
    ],
    'displayErrorDetails' => false
];


use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//Load all composer libs
include_once '../vendor/autoload.php';

date_default_timezone_set('America/Chicago');

ob_start();
session_start();

$config = [
    'settings' => $settings
];

//Get our app running
$app = new \Slim\App($config);

$container = $app->getContainer();

include_once __DIR__ . '/containers/containers.php';
include_once __DIR__ . '/routes/routes.php';

include_once __DIR__ . '/middleware/gsMiddleware.php';
$app->add(new sign\middleware\gsMiddleware($container));