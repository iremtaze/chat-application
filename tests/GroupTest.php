<?php

use PHPUnit\Framework\TestCase;
use App\Models\GroupModel;
use App\Models\UserModel;

class GroupTest extends TestCase
{
    private $db;
    private $groupModel;
    private $userModel;
    private $userId;
    
    protected function setUp(): void
    {
        //Create an in-memory SQLite database for testing
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        //Create the models with the test database
        $this->groupModel = new GroupModel($this->db);
        $this->userModel = new UserModel($this->db);
        
        //Create a test user
        $this->userId = $this->userModel->create('testuser');
    }
    
    public function testCreateGroup()
    {
        //Create a group
        $groupName = 'Test Group';
        $groupId = $this->groupModel->create($groupName, $this->userId);
        
        //Check that the group was created with a valid ID
        $this->assertIsInt($groupId);
        $this->assertGreaterThan(0, $groupId);
        
        //Get the group by ID
        $group = $this->groupModel->getById($groupId);
        
        //Check that the group exists and has the correct name
        $this->assertNotNull($group);
        $this->assertEquals($groupName, $group['name']);
        $this->assertEquals($this->userId, $group['created_by']);
    }
    
    public function testGetAllGroups()
    {
        //Create multiple groups
        $groupId1 = $this->groupModel->create('Group 1', $this->userId);
        $groupId2 = $this->groupModel->create('Group 2', $this->userId);
        
        //Get all groups
        $groups = $this->groupModel->getAll();
        
        //Check that all groups are returned
        $this->assertIsArray($groups);
        $this->assertCount(2, $groups);
        
        //Check group IDs match
        $groupIds = array_column($groups, 'id');
        $this->assertContains($groupId1, $groupIds);
        $this->assertContains($groupId2, $groupIds);
    }
    
    public function testAddMember()
    {
        //Create a group
        $groupId = $this->groupModel->create('Member Test Group', $this->userId);
        
        //Create another user
        $otherUserId = $this->userModel->create('anotheruser');
        
        //Add the user to the group
        $result = $this->groupModel->addMember($groupId, $otherUserId);
        
        //Check that the member was added successfully
        $this->assertTrue($result);
        
        //Check that the user is now a member of the group
        $members = $this->groupModel->getMembers($groupId);
        $this->assertIsArray($members);
        
        //User IDs are in the 'id' column in the result
        $memberIds = array_column($members, 'id');
        $this->assertContains($otherUserId, $memberIds);
    }
    
    public function testIsMember()
    {
        //Create a group
        $groupId = $this->groupModel->create('Membership Test Group', $this->userId);
        
        //Create another user
        $otherUserId = $this->userModel->create('memberuser');
        
        //Check that the user is not yet a member
        $isMember = $this->groupModel->isMember($groupId, $otherUserId);
        $this->assertFalse($isMember);
        
        //Add the user to the group
        $this->groupModel->addMember($groupId, $otherUserId);
        
        //Check that the user is now a member
        $isMember = $this->groupModel->isMember($groupId, $otherUserId);
        $this->assertTrue($isMember);
    }
} 