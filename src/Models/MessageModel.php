<?php

namespace App\Models;

class MessageModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->initTable();
    }

    private function initTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            group_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (group_id) REFERENCES groups(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )";
        
        $this->db->exec($sql);
    }

    public function create(int $groupId, int $userId, string $content): int
    {
        if ($this->db instanceof \SQLite3) {
            $stmt = $this->db->prepare("
                INSERT INTO messages (group_id, user_id, content) 
                VALUES (:group_id, :user_id, :content)
            ");
            
            $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
            $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
            $stmt->bindValue(':content', $content, SQLITE3_TEXT);
            $stmt->execute();
            
            return $this->db->lastInsertRowID();
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO messages (group_id, user_id, content) 
                VALUES (:group_id, :user_id, :content)
            ");
            
            $stmt->execute([
                ':group_id' => $groupId,
                ':user_id' => $userId,
                ':content' => $content
            ]);
            
            return $this->db->lastInsertId();
        }
    }

    public function getByGroupId(int $groupId, ?int $limit = null, ?int $offset = null): array
    {
        if ($this->db instanceof \SQLite3) {
            $sql = "
                SELECT m.id, m.content, m.created_at, u.id as user_id, u.username
                FROM messages m
                JOIN users u ON m.user_id = u.id
                WHERE m.group_id = :group_id
                ORDER BY m.created_at DESC
            ";
            
            if ($limit !== null) {
                $sql .= " LIMIT " . intval($limit);
                if ($offset !== null) {
                    $sql .= " OFFSET " . intval($offset);
                }
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
            $result = $stmt->execute();
            
            $messages = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $messages[] = $row;
            }
            
            return $messages;
        } else {
            $sql = "
                SELECT m.id, m.content, m.created_at, u.id as user_id, u.username
                FROM messages m
                JOIN users u ON m.user_id = u.id
                WHERE m.group_id = :group_id
                ORDER BY m.created_at DESC
            ";
            
            if ($limit !== null) {
                $sql .= " LIMIT :limit";
                if ($offset !== null) {
                    $sql .= " OFFSET :offset";
                }
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':group_id', $groupId, \PDO::PARAM_INT);
            
            if ($limit !== null) {
                $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
                if ($offset !== null) {
                    $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }

    public function getById(int $id): ?array
    {
        if ($this->db instanceof \SQLite3) {
            $stmt = $this->db->prepare("
                SELECT m.*, u.username 
                FROM messages m
                JOIN users u ON m.user_id = u.id
                WHERE m.id = :id
            ");
            
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $result = $stmt->execute();
            
            $message = $result->fetchArray(SQLITE3_ASSOC);
            return $message ?: null;
        } else {
            $stmt = $this->db->prepare("
                SELECT m.*, u.username 
                FROM messages m
                JOIN users u ON m.user_id = u.id
                WHERE m.id = :id
            ");
            
            $stmt->execute([':id' => $id]);
            $message = $stmt->fetch();
            
            return $message ?: null;
        }
    }

    /**
     * Get a message by ID with the sender's username
     */
    public function getByIdWithUsername(int $id): ?array
    {
        if ($this->db instanceof \SQLite3) {
            $stmt = $this->db->prepare("
                SELECT m.*, u.username 
                FROM messages m
                JOIN users u ON m.user_id = u.id
                WHERE m.id = :id
            ");
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $result = $stmt->execute();
            
            $message = $result->fetchArray(SQLITE3_ASSOC);
            return $message ?: null;
        } else {
            $stmt = $this->db->prepare("
                SELECT m.*, u.username 
                FROM messages m
                JOIN users u ON m.user_id = u.id
                WHERE m.id = :id
            ");
            $stmt->execute([':id' => $id]);
            
            $message = $stmt->fetch();
            return $message ?: null;
        }
    }

    /**
     * Get all messages for a group with usernames
     */
    public function getByGroupIdWithUsername(int $groupId): array
    {
        if ($this->db instanceof \SQLite3) {
            $stmt = $this->db->prepare("
                SELECT m.*, u.username 
                FROM messages m
                JOIN users u ON m.user_id = u.id
                WHERE m.group_id = :group_id
                ORDER BY m.created_at DESC
            ");
            $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
            $result = $stmt->execute();
            
            $messages = [];
            while ($message = $result->fetchArray(SQLITE3_ASSOC)) {
                $messages[] = $message;
            }
            
            return $messages;
        } else {
            $stmt = $this->db->prepare("
                SELECT m.*, u.username 
                FROM messages m
                JOIN users u ON m.user_id = u.id
                WHERE m.group_id = :group_id
                ORDER BY m.created_at DESC
            ");
            $stmt->execute([':group_id' => $groupId]);
            
            return $stmt->fetchAll();
        }
    }
} 