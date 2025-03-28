<?php

namespace App\Models;

class UserModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->initTable();
    }

    private function initTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            token TEXT UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->db->exec($sql);
    }

    public function create(string $username): int
    {
        $token = bin2hex(random_bytes(16));
        
        //Check if username already exists
        $existingUser = $this->getByUsername($username);
        if ($existingUser) {
            throw new \Exception("Username already exists");
        }
        
        if ($this->db instanceof \SQLite3) {
            $stmt = $this->db->prepare("INSERT INTO users (username, token) VALUES (:username, :token)");
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            $stmt->bindValue(':token', $token, SQLITE3_TEXT);
            $stmt->execute();
            
            return $this->db->lastInsertRowID();
        } else {
            $stmt = $this->db->prepare("INSERT INTO users (username, token) VALUES (:username, :token)");
            $stmt->execute([
                ':username' => $username,
                ':token' => $token
            ]);
            
            return $this->db->lastInsertId();
        }
    }

    public function getById(int $id): ?array
    {
        if ($this->db instanceof \SQLite3) {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $result = $stmt->execute();
            
            $user = $result->fetchArray(SQLITE3_ASSOC);
            return $user ?: null;
        } else {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            $user = $stmt->fetch();
            return $user ?: null;
        }
    }

    public function getByToken(string $token): ?array
    {
        if ($this->db instanceof \SQLite3) {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE token = :token");
            $stmt->bindValue(':token', $token, SQLITE3_TEXT);
            $result = $stmt->execute();
            
            $user = $result->fetchArray(SQLITE3_ASSOC);
            return $user ?: null;
        } else {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE token = :token");
            $stmt->execute([':token' => $token]);
            
            $user = $stmt->fetch();
            return $user ?: null;
        }
    }

    public function getByUsername(string $username): ?array
    {
        if ($this->db instanceof \SQLite3) {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            $result = $stmt->execute();
            
            $user = $result->fetchArray(SQLITE3_ASSOC);
            return $user ?: null;
        } else {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            
            $user = $stmt->fetch();
            return $user ?: null;
        }
    }

    public function getAll(): array
    {
        if ($this->db instanceof \SQLite3) {
            $result = $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
            
            $users = [];
            while ($user = $result->fetchArray(SQLITE3_ASSOC)) {
                $users[] = $user;
            }
            
            return $users;
        } else {
            $stmt = $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
            return $stmt->fetchAll();
        }
    }
} 