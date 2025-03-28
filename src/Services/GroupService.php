<?php

namespace App\Services;

use App\Models\GroupModel;

class GroupService
{
    private $groupModel;

    public function __construct(GroupModel $groupModel)
    {
        $this->groupModel = $groupModel;
    }

    public function createGroup(string $name, int $userId): array
    {
        //Validate input
        if (empty($name)) {
            throw new \InvalidArgumentException("Group name cannot be empty");
        }

        //Create the group
        $groupId = $this->groupModel->create($name, $userId);
        return $this->getGroup($groupId);
    }

    public function getAllGroups(): array
    {
        return $this->groupModel->getAll();
    }

    public function getGroup(int $id): ?array
    {
        $group = $this->groupModel->getById($id);
        
        if (!$group) {
            return null;
        }
        
        //Add members to the group data
        $group['members'] = $this->groupModel->getMembers($id);
        
        return $group;
    }

    public function joinGroup(int $groupId, int $userId): bool
    {
        //Check if group exists
        $group = $this->groupModel->getById($groupId);
        if (!$group) {
            throw new \Exception("Group not found");
        }
        
        //Add user to the group
        return $this->groupModel->addMember($groupId, $userId);
    }

    public function isMember(int $groupId, int $userId): bool
    {
        return $this->groupModel->isMember($groupId, $userId);
    }
} 