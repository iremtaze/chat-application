<?php

use PHPUnit\Framework\TestCase;
use App\Models\UserModel;

class UserTest extends TestCase
{
    private $db;
    private $userModel;
    
    protected function setUp(): void
    {
        //Create an in-memory SQLite database for testing
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        //Create the user model with the test database
        $this->userModel = new UserModel($this->db);
    }
    
    public function testCreateUser()
    {
        //Create a user
        $username = 'testuser';
        $userId = $this->userModel->create($username);
        
        //Check that the user was created with a valid ID
        $this->assertIsInt($userId);
        $this->assertGreaterThan(0, $userId);
        
        //Get the user by ID
        $user = $this->userModel->getById($userId);
        
        //Check that the user exists and has the correct username
        $this->assertNotNull($user);
        $this->assertEquals($username, $user['username']);
        $this->assertArrayHasKey('token', $user);
    }
    
    public function testGetByToken()
    {
        //Create a user
        $username = 'tokenuser';
        $userId = $this->userModel->create($username);
        
        //Get the user by ID to retrieve the token
        $user = $this->userModel->getById($userId);
        $token = $user['token'];
        
        //Get the user by token
        $userByToken = $this->userModel->getByToken($token);
        
        //Check that the user retrieved by token is the same as the one created
        $this->assertNotNull($userByToken);
        $this->assertEquals($userId, $userByToken['id']);
        $this->assertEquals($username, $userByToken['username']);
    }
    
    public function testGetByUsername()
    {
        //Create a user
        $username = 'usernameuser';
        $userId = $this->userModel->create($username);
        
        //Get the user by username
        $userByUsername = $this->userModel->getByUsername($username);
        
        //Check that the user retrieved by username is the same as the one created
        $this->assertNotNull($userByUsername);
        $this->assertEquals($userId, $userByUsername['id']);
    }
    
    public function testGetAll()
    {
        // Create multiple users
        $username1 = 'user1';
        $username2 = 'user2';
        $userId1 = $this->userModel->create($username1);
        $userId2 = $this->userModel->create($username2);
        
        // Get all users
        $users = $this->userModel->getAll();
        
        // Check that the users list is an array
        $this->assertIsArray($users);
        
        // Check that the created users are in the list
        $userIds = array_column($users, 'id');
        $usernames = array_column($users, 'username');
        
        $this->assertContains($userId1, $userIds);
        $this->assertContains($userId2, $userIds);
        $this->assertContains($username1, $usernames);
        $this->assertContains($username2, $usernames);
        
        // Check that we have exactly the number of users we created
        $this->assertCount(2, $users);
    }
} 