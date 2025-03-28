<?php

namespace App\Models;

class GroupModel
{
    private $db;
    private $groupsTable = 'groups';
    private $membersTable = 'group_members';

    public function __construct($db)
    {
        $this->db = $db;
        $this->initTable();
    }

    private function initTable()
    {
        //Create groups table
        $sql = "CREATE TABLE IF NOT EXISTS groups (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            created_by INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id)
        )";
        $this->db->exec($sql);
        
        //Create group_members table
        $sql = "CREATE TABLE IF NOT EXISTS group_members (
            group_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (group_id, user_id),
            FOREIGN KEY (group_id) REFERENCES groups(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )";
        $this->db->exec($sql);
    }

    public function create(string $name, int $userId): int
    {
        if ($this->db instanceof \SQLite3) {
            $this->db->exec('BEGIN TRANSACTION');

            try {
                //Create the group
                $stmt = $this->db->prepare("INSERT INTO groups (name, created_by) VALUES (:name, :created_by)");
                $stmt->bindValue(':name', $name, SQLITE3_TEXT);
                $stmt->bindValue(':created_by', $userId, SQLITE3_INTEGER);
                $stmt->execute();
                
                $groupId = $this->db->lastInsertRowID();
                
                //Add creator as a member
                $this->addMember($groupId, $userId);
                
                $this->db->exec('COMMIT');
                return $groupId;
            } catch (\Exception $e) {
                $this->db->exec('ROLLBACK');
                throw $e;
            }
        } else {
            $this->db->beginTransaction();

            try {
                //Create the group
                $stmt = $this->db->prepare("INSERT INTO groups (name, created_by) VALUES (:name, :created_by)");
                $stmt->execute([
                    ':name' => $name,
                    ':created_by' => $userId
                ]);
                
                $groupId = $this->db->lastInsertId();
                
                //Add creator as a member
                $this->addMember($groupId, $userId);
                
                $this->db->commit();
                return $groupId;
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        }
    }

    public function getAll(): array
    {
        if ($this->db instanceof \SQLite3) {
            $result = $this->db->query("SELECT * FROM groups ORDER BY created_at DESC");
            $groups = [];
            
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $groups[] = $row;
            }
            
            return $groups;
        } else {
            $stmt = $this->db->query("SELECT * FROM groups ORDER BY created_at DESC");
            return $stmt->fetchAll();
        }
    }

    public function getById(int $id): ?array
    {
        if ($this->db instanceof \SQLite3) {
            $stmt = $this->db->prepare("SELECT * FROM groups WHERE id = :id");
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $result = $stmt->execute();
            
            $group = $result->fetchArray(SQLITE3_ASSOC);
            return $group ?: null;
        } else {
            $stmt = $this->db->prepare("SELECT * FROM groups WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            $group = $stmt->fetch();
            return $group ?: null;
        }
    }

    public function addMember(int $groupId, int $userId): bool
    {
        if ($this->db instanceof \SQLite3) {
            //Check if the user is already a member
            $stmt = $this->db->prepare("SELECT 1 FROM group_members WHERE group_id = :group_id AND user_id = :user_id");
            $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
            $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
            $result = $stmt->execute();
            
            if ($result->fetchArray(SQLITE3_ASSOC)) {
                return false; // Already a member
            }
            
            //Add user to the group
            $stmt = $this->db->prepare("INSERT INTO group_members (group_id, user_id) VALUES (:group_id, :user_id)");
            $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
            $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
            $stmt->execute();
            
            return true;
        } else {
            //Check if the user is already a member
            $stmt = $this->db->prepare("SELECT 1 FROM group_members WHERE group_id = :group_id AND user_id = :user_id");
            $stmt->execute([
                ':group_id' => $groupId,
                ':user_id' => $userId
            ]);
            
            if ($stmt->fetch()) {
                return false; //Already a member
            }
            
            //Add user to the group
            $stmt = $this->db->prepare("INSERT INTO group_members (group_id, user_id) VALUES (:group_id, :user_id)");
            $stmt->execute([
                ':group_id' => $groupId,
                ':user_id' => $userId
            ]);
            
            return true;
        }
    }

    public function getMembers(int $groupId): array
    {
        if ($this->db instanceof \SQLite3) {
            $stmt = $this->db->prepare("
                SELECT u.id, u.username, gm.joined_at 
                FROM group_members gm
                JOIN users u ON gm.user_id = u.id
                WHERE gm.group_id = :group_id
                ORDER BY gm.joined_at
            ");
            
            $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
            $result = $stmt->execute();
            
            $members = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $members[] = $row;
            }
            
            return $members;
        } else {
            $stmt = $this->db->prepare("
                SELECT u.id, u.username, gm.joined_at 
                FROM group_members gm
                JOIN users u ON gm.user_id = u.id
                WHERE gm.group_id = :group_id
                ORDER BY gm.joined_at
            ");
            $stmt->execute([':group_id' => $groupId]);
            
            return $stmt->fetchAll();
        }
    }

    public function isMember(int $groupId, int $userId): bool
    {
        if ($this->db instanceof \SQLite3) {
            $stmt = $this->db->prepare("
                SELECT 1 FROM group_members 
                WHERE group_id = :group_id AND user_id = :user_id
            ");
            
            $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
            $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
            $result = $stmt->execute();
            
            return (bool) $result->fetchArray(SQLITE3_ASSOC);
        } else {
            $stmt = $this->db->prepare("
                SELECT 1 FROM group_members 
                WHERE group_id = :group_id AND user_id = :user_id
            ");
            $stmt->execute([
                ':group_id' => $groupId,
                ':user_id' => $userId
            ]);
            
            return (bool) $stmt->fetch();
        }
    }
} 