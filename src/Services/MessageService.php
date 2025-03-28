<?php

namespace App\Services;

use App\Models\MessageModel;
use App\Models\GroupModel;

class MessageService
{
    private $messageModel;
    private $groupModel;

    public function __construct(MessageModel $messageModel, ?GroupModel $groupModel = null)
    {
        $this->messageModel = $messageModel;
        $this->groupModel = $groupModel;
    }

    public function createMessage(int $groupId, int $userId, string $content): array
    {
        //Validate input
        if (empty($content)) {
            throw new \InvalidArgumentException("Message content cannot be empty");
        }
        
        //Check if user is a member of the group
        if ($this->groupModel && !$this->groupModel->isMember($groupId, $userId)) {
            throw new \Exception("User is not a member of the group");
        }
        
        //Create the message
        $messageId = $this->messageModel->create($groupId, $userId, $content);
        return $this->getMessage($messageId);
    }

    public function getMessagesByGroup(int $groupId, ?int $limit = 50, ?int $offset = 0): array
    {
        return $this->messageModel->getByGroupId($groupId, $limit, $offset);
    }

    public function getMessage(int $id): ?array
    {
        return $this->messageModel->getById($id);
    }
} 