<?php

use Slim\Factory\AppFactory;
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();

$dependencies = require __DIR__ . '/../src/dependencies.php';
foreach ($dependencies as $key => $definition) {
    $container->set($key, $definition);
}

AppFactory::setContainer($container);
$app = AppFactory::create();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

require __DIR__ . '/../src/routes.php';

$app->run(); 