<?php

use PHPUnit\Framework\TestCase;
use App\Models\MessageModel;
use App\Models\GroupModel;
use App\Models\UserModel;

class MessageTest extends TestCase
{
    private $db;
    private $messageModel;
    private $groupModel;
    private $userModel;
    private $userId;
    private $groupId;
    
    protected function setUp(): void
    {
        //Create an in-memory SQLite database for testing
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        //Create the models with the test database
        $this->messageModel = new MessageModel($this->db);
        $this->groupModel = new GroupModel($this->db);
        $this->userModel = new UserModel($this->db);
        
        //Create a test user
        $this->userId = $this->userModel->create('testuser');
        
        //Create a test group
        $this->groupId = $this->groupModel->create('Test Group', $this->userId);
        
        //Add the user to the group
        $this->groupModel->addMember($this->groupId, $this->userId);
    }
    
    public function testCreateMessage()
    {
        //Create a message
        $content = 'Test message content';
        $messageId = $this->messageModel->create($this->groupId, $this->userId, $content);
        
        //Check that the message was created with a valid ID
        $this->assertIsInt($messageId);
        $this->assertGreaterThan(0, $messageId);
        
        //Get the message by ID
        $message = $this->messageModel->getById($messageId);
        
        //Check that the message exists and has the correct properties
        $this->assertNotNull($message);
        $this->assertEquals($content, $message['content']);
        $this->assertEquals($this->userId, $message['user_id']);
        $this->assertEquals($this->groupId, $message['group_id']);
    }
    
    public function testGetMessagesByGroup()
    {
        //Create multiple messages
        $messageId1 = $this->messageModel->create($this->groupId, $this->userId, 'Message 1');
        $messageId2 = $this->messageModel->create($this->groupId, $this->userId, 'Message 2');
        
        //Get messages by group
        $messages = $this->messageModel->getByGroupId($this->groupId);
        
        //Check that all messages are returned
        $this->assertIsArray($messages);
        $this->assertCount(2, $messages);
        
        //Check message IDs match
        $messageIds = array_column($messages, 'id');
        $this->assertContains($messageId1, $messageIds);
        $this->assertContains($messageId2, $messageIds);
    }
    
    public function testMessageWithUsername()
    {
        //Create a message
        $messageId = $this->messageModel->create($this->groupId, $this->userId, 'Test message with username');
        
        //Get the message with username
        $message = $this->messageModel->getByIdWithUsername($messageId);
        
        //Check that the message includes the username
        $this->assertNotNull($message);
        $this->assertArrayHasKey('username', $message);
        $this->assertEquals('testuser', $message['username']);
    }
    
    public function testMessagesByGroupWithUsername()
    {
        //Create a message
        $this->messageModel->create($this->groupId, $this->userId, 'Group message with username');
        
        //Get group messages with usernames
        $messages = $this->messageModel->getByGroupIdWithUsername($this->groupId);
        
        //Check that at least one message is returned
        $this->assertIsArray($messages);
        $this->assertGreaterThan(0, count($messages));
        
        //Check that the messages include usernames
        foreach ($messages as $message) {
            $this->assertArrayHasKey('username', $message);
        }
    }
} 