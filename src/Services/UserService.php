<?php

namespace App\Services;

use App\Models\UserModel;

class UserService
{
    private $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public function createUser(string $username): array
    {
        //Check if username already exists
        $existingUser = $this->userModel->getByUsername($username);
        if ($existingUser) {
            throw new \Exception("Username already exists");
        }

        //Create the user
        $userId = $this->userModel->create($username);
        $user = $this->userModel->getById($userId);
        
        return $user;
    }

    public function getUser(int $id): ?array
    {
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            return null;
        }
        
        return $user;
    }

    public function validateUserToken(string $token): ?array
    {
        return $this->userModel->getByToken($token);
    }

    public function getAllUsers(): array
    {
        return $this->userModel->getAll();
    }
} 