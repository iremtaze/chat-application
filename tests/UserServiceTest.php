<?php

use PHPUnit\Framework\TestCase;
use App\Models\UserModel;
use App\Services\UserService;

class UserServiceTest extends TestCase
{
    private $db;
    private $userModel;
    private $userService;
    
    protected function setUp(): void
    {
        //Create an in memory SQLite database for testing
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        //Create the model and service with the test database
        $this->userModel = new UserModel($this->db);
        $this->userService = new UserService($this->userModel);
    }
    
    public function testCreateUser()
    {
        //Create a user
        $username = 'serviceuser';
        $user = $this->userService->createUser($username);
        
        //Check that the user was created with the correct properties
        $this->assertIsArray($user);
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('username', $user);
        $this->assertArrayHasKey('token', $user);
        $this->assertEquals($username, $user['username']);
    }
    
    public function testCreateDuplicateUser()
    {
        //create a user
        $username = 'dupuser';
        $this->userService->createUser($username);
        
        //Try to create a user with the same username
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Username already exists');
        $this->userService->createUser($username);
    }
    
    public function testGetUser()
    {
        //Create a user
        $username = 'getuser';
        $createdUser = $this->userService->createUser($username);
        
        //Get the user by ID
        $user = $this->userService->getUser($createdUser['id']);
        
        //Check that the user is retrieved correctly
        $this->assertIsArray($user);
        $this->assertEquals($createdUser['id'], $user['id']);
        $this->assertEquals($username, $user['username']);
    }
    
    public function testGetNonExistentUser()
    {
        //Try to get a user that doesn't exist
        $user = $this->userService->getUser(999);
        
        //Check that null is returned
        $this->assertNull($user);
    }
    
    public function testValidateUserToken()
    {
        //Create a user
        $username = 'tokenuser';
        $createdUser = $this->userService->createUser($username);
        
        //Validate the user's token
        $user = $this->userService->validateUserToken($createdUser['token']);
        
        //Check that the user is validated correctly
        $this->assertIsArray($user);
        $this->assertEquals($createdUser['id'], $user['id']);
    }
    
    public function testValidateInvalidToken()
    {
        //Try to validate an invalid token
        $user = $this->userService->validateUserToken('invalid-token');
        
        //Check that null is returned
        $this->assertNull($user);
    }
    
    public function testGetAllUsers()
    {
        // Create multiple users
        $username1 = 'allusers1';
        $username2 = 'allusers2';
        $user1 = $this->userService->createUser($username1);
        $user2 = $this->userService->createUser($username2);
        
        // Get all users
        $users = $this->userService->getAllUsers();
        
        // Check that the users list is an array
        $this->assertIsArray($users);
        
        // Check that the created users are in the list
        $userIds = array_column($users, 'id');
        $usernames = array_column($users, 'username');
        
        $this->assertContains($user1['id'], $userIds);
        $this->assertContains($user2['id'], $userIds);
        $this->assertContains($username1, $usernames);
        $this->assertContains($username2, $usernames);
        
        // Check that we have at least the number of users we created
        $this->assertGreaterThanOrEqual(2, count($users));
    }
} 