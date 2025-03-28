<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\UserController;
use App\Controllers\GroupController;
use App\Controllers\MessageController;

//Test route
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode(['message' => 'Chat API is running']));
    return $response->withHeader('Content-Type', 'application/json');
});

//User routes
$app->group('/api/users', function (RouteCollectorProxy $group) {
    $group->get('', [UserController::class, 'getAll']);
    $group->post('', [UserController::class, 'create']);
    $group->get('/{id}', [UserController::class, 'get']);
});

//Group routes
$app->group('/api/groups', function (RouteCollectorProxy $group) {
    $group->get('', [GroupController::class, 'getAll']);
    $group->post('', [GroupController::class, 'create']);
    $group->get('/{id}', [GroupController::class, 'get']);
    $group->post('/{id}/join', [GroupController::class, 'join']);
});

//Message routes
$app->group('/api/groups/{groupId}/messages', function (RouteCollectorProxy $group) {
    $group->get('', [MessageController::class, 'getByGroup']);
    $group->post('', [MessageController::class, 'create']);
}); 