<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\MessageService;
use App\Services\GroupService;
use App\Services\UserService;

class MessageController
{
    private $messageService;
    private $groupService;
    private $userService;

    public function __construct(
        MessageService $messageService, 
        ?GroupService $groupService = null,
        ?UserService $userService = null
    ) {
        $this->messageService = $messageService;
        $this->groupService = $groupService;
        $this->userService = $userService;
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        $groupId = (int) $args['groupId'];
        $data = $request->getParsedBody();
        
        //Validate input
        if (!isset($data['content']) || empty($data['content'])) {
            $response->getBody()->write(json_encode([
                'error' => 'Message content is required'
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
        
        //Check if user is member of the group
        if ($this->groupService && !$this->groupService->isMember($groupId, $user['id'])) {
            $response->getBody()->write(json_encode([
                'error' => 'You must be a member of the group to send messages'
            ]));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }
        
        try {
            //Create the message
            $message = $this->messageService->createMessage($groupId, $user['id'], $data['content']);
            
            $response->getBody()->write(json_encode([
                'message' => 'Message sent successfully',
                'data' => $message
            ]));
            
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function getByGroup(Request $request, Response $response, array $args): Response
    {
        $groupId = (int) $args['groupId'];
        
        //Get query parameters
        $queryParams = $request->getQueryParams();
        $limit = isset($queryParams['limit']) ? (int) $queryParams['limit'] : 50;
        $offset = isset($queryParams['offset']) ? (int) $queryParams['offset'] : 0;
        
        try {
            //Get messages
            $messages = $this->messageService->getMessagesByGroup($groupId, $limit, $offset);
            
            $response->getBody()->write(json_encode([
                'messages' => $messages
            ]));
            
            return $response->withHeader('Content-Type', 'application/json');
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