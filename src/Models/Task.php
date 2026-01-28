<?php
/**
 * Task Model - Task management
 */

namespace App\Models;

use App\Config\Database;
use PDO;

class Task
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Get all tasks
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            "SELECT id, title, description, status, created_at, updated_at 
             FROM tasks 
             ORDER BY created_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a task by ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, title, description, status, created_at, updated_at 
             FROM tasks 
             WHERE id = ?"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Create a new task
     */
    public function create(string $title, string $description = '', string $status = 'pending'): int|false
    {
        if (empty(trim($title))) {
            throw new \InvalidArgumentException('Title is required');
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO tasks (title, description, status, created_at, updated_at) 
             VALUES (?, ?, ?, NOW(), NOW())"
        );
        
        if ($stmt->execute([$title, $description, $status])) {
            return $this->pdo->lastInsertId();
        }
        
        return false;
    }

    /**
     * Update a task
     */
    public function update(int $id, string $title, string $description = '', string $status = null): bool
    {
        if (empty(trim($title))) {
            throw new \InvalidArgumentException('Title is required');
        }

        $query = "UPDATE tasks SET title = ?, description = ?, updated_at = NOW()";
        $params = [$title, $description];

        if ($status !== null) {
            $query .= ", status = ?";
            $params[] = $status;
        }

        $query .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Toggle task status
     */
    public function toggleStatus(int $id): bool
    {
        $task = $this->getById($id);
        if (!$task) {
            return false;
        }

        $newStatus = $task['status'] === 'completed' ? 'pending' : 'completed';
        return $this->update($id, $task['title'], $task['description'], $newStatus);
    }

    /**
     * Delete a task
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        $stmt = $this->pdo->query(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
             FROM tasks"
        );
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'completed' => 0, 'pending' => 0];
    }
}
