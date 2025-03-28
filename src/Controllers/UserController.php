<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\UserService;

class UserController
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        //Validate input
        if (!isset($data['username']) || empty($data['username'])) {
            $response->getBody()->write(json_encode([
                'error' => 'Username is required'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        try {
            //Create the user
            $user = $this->userService->createUser($data['username']);
            
            $response->getBody()->write(json_encode([
                'message' => 'User created successfully',
                'user' => $user
            ]));
            
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        $user = $this->userService->getUser($id);
        
        if (!$user) {
            $response->getBody()->write(json_encode([
                'error' => 'User not found'
            ]));
            
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        $response->getBody()->write(json_encode($user));
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getAll(Request $request, Response $response): Response
    {
        $users = $this->userService->getAllUsers();
        
        $response->getBody()->write(json_encode([
            'users' => $users
        ]));
        
        return $response->withHeader('Content-Type', 'application/json');
    }
} 