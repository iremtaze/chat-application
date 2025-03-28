<?php

namespace App\Controllers;

use App\Models\Group;
use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\GroupService;
use App\Services\UserService;
use PDO;

class GroupController
{
    private $groupService;
    private $userService;

    public function __construct(GroupService $groupService, ?UserService $userService = null)
    {
        $this->groupService = $groupService;
        $this->userService = $userService;
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        //Validate input
        if (!isset($data['name']) || empty($data['name'])) {
            $response->getBody()->write(json_encode([
                'error' => 'Group name is required'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        //Authenticate user
        $user = $this->authenticateUser($request);
        if (!$user) {
            $response->getBody()->write(json_encode([
                'error' => 'Authentication required'
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        
        try {
            //Create the group
            $group = $this->groupService->createGroup($data['name'], $user['id']);
            
            $response->getBody()->write(json_encode([
                'message' => 'Group created successfully',
                'group' => $group
            ]));
            
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function getAll(Request $request, Response $response): Response
    {
        $groups = $this->groupService->getAllGroups();
        
        $response->getBody()->write(json_encode([
            'groups' => $groups
        ]));
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        $group = $this->groupService->getGroup($id);
        
        if (!$group) {
            $response->getBody()->write(json_encode([
                'error' => 'Group not found'
            ]));
            
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        $response->getBody()->write(json_encode($group));
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function join(Request $request, Response $response, array $args): Response
    {
        $groupId = (int) $args['id'];
        
        //Authenticate user
        $user = $this->authenticateUser($request);
        if (!$user) {
            $response->getBody()->write(json_encode([
                'error' => 'Authentication required'
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        
        try {
            //Join the group
            $result = $this->groupService->joinGroup($groupId, $user['id']);
            
            if ($result) {
                $response->getBody()->write(json_encode([
                    'message' => 'Successfully joined the group'
                ]));
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                $response->getBody()->write(json_encode([
                    'message' => 'Already a member of the group'
                ]));
                return $response->withHeader('Content-Type', 'application/json');
            }
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    private function authenticateUser(Request $request): ?array
    {
        if (!$this->userService) {
            return null;
        }
        
        $token = $request->getHeaderLine('Authorization');
        
        if (empty($token)) {
            return null;
        }
        
        //Extract token from Bearer format
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        return $this->userService->validateUserToken($token);
    }
} 