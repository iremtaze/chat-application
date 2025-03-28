<?php

use PHPUnit\Framework\TestCase;
use App\Controllers\UserController;
use App\Services\UserService;
use App\Models\UserModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;
use Slim\Psr7\Factory\RequestFactory;

class UserControllerTest extends TestCase
{
    private $db;
    private $userModel;
    private $userService;
    private $userController;
    
    protected function setUp(): void
    {
        //Create an in-memory SQLite database for testing
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        //Create the model, service, and controller with the test database
        $this->userModel = new UserModel($this->db);
        $this->userService = new UserService($this->userModel);
        $this->userController = new UserController($this->userService);
    }
    
    public function testCreateUser()
    {
        //Create a request with a username
        $requestFactory = new RequestFactory();
        $request = $requestFactory->createRequest('POST', '/api/users');
        $request = $request->withParsedBody(['username' => 'controlleruser']);
        
        //Create a response
        $response = new Response();
        
        //Call the controller method
        $response = $this->userController->create($request, $response);
        
        //Check the response status and body
        $this->assertEquals(201, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('user', $data);
        $this->assertEquals('User created successfully', $data['message']);
        $this->assertEquals('controlleruser', $data['user']['username']);
    }
    
    public function testCreateUserWithoutUsername()
    {
        //Create a request without a username
        $requestFactory = new RequestFactory();
        $request = $requestFactory->createRequest('POST', '/api/users');
        $request = $request->withParsedBody([]);
        
        //Create a response
        $response = new Response();
        
        //Call the controller method
        $response = $this->userController->create($request, $response);
        
        //Check the response status and body
        $this->assertEquals(400, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Username is required', $data['error']);
    }
    
    public function testGetUser()
    {
        //Create a user
        $userId = $this->userModel->create('getuser');
        
        //Create a request
        $requestFactory = new RequestFactory();
        $request = $requestFactory->createRequest('GET', "/api/users/{$userId}");
        
        //Create a response
        $response = new Response();
        
        //Call the controller method
        $response = $this->userController->get($request, $response, ['id' => $userId]);
        
        //Check the response status and body
        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        
        $this->assertEquals($userId, $data['id']);
        $this->assertEquals('getuser', $data['username']);
    }
    
    public function testGetNonExistentUser()
    {
        //Create a request for a non-existent user
        $requestFactory = new RequestFactory();
        $request = $requestFactory->createRequest('GET', '/api/users/999');
        
        //Create a response
        $response = new Response();
        
        //Call the controller method
        $response = $this->userController->get($request, $response, ['id' => 999]);
        
        //Check the response status and body
        $this->assertEquals(404, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('User not found', $data['error']);
    }
    
    public function testGetAllUsers()
    {
        //Create multiple users
        $userId1 = $this->userModel->create('user1');
        $userId2 = $this->userModel->create('user2');
        
        // create a request
        $requestFactory = new RequestFactory();
        $request = $requestFactory->createRequest('GET', '/api/users');
        
        //Create a response
        $response = new Response();
        
        //Call the controller method
        $response = $this->userController->getAll($request, $response);
        
        //Check the response status and body
        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        
        //Check that the response contains users array
        $this->assertArrayHasKey('users', $data);
        $this->assertIsArray($data['users']);
        
        //Check that the created users are in the list
        $userIds = array_column($data['users'], 'id');
        $usernames = array_column($data['users'], 'username');
        
        $this->assertContains($userId1, $userIds);
        $this->assertContains($userId2, $userIds);
        $this->assertContains('user1', $usernames);
        $this->assertContains('user2', $usernames);
    }
} 