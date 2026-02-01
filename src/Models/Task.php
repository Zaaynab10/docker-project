<?php

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
            "SELECT id, title, description, status, task_order, created_at, updated_at 
             FROM tasks 
             ORDER BY status ASC, task_order ASC, created_at DESC"
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

        // Get the next order for this status
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(MAX(task_order), -1) + 1 FROM tasks WHERE status = ?"
        );
        $stmt->execute([$status]);
        $order = $stmt->fetchColumn();

        $stmt = $this->pdo->prepare(
            "INSERT INTO tasks (title, description, status, task_order, created_at, updated_at) 
             VALUES (?, ?, ?, ?, NOW(), NOW())"
        );
        
        if ($stmt->execute([$title, $description, $status, $order])) {
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
     * Reorder tasks within a status
     * @param array $taskIds Ordered array of task IDs for a specific status
     * @param string $status The status these tasks belong to
     */
    public function reorder(array $taskIds, string $status): bool
    {
        if (empty($taskIds)) {
            return true;
        }

        $stmt = $this->pdo->prepare(
            "UPDATE tasks SET task_order = ?, updated_at = NOW() WHERE id = ? AND status = ?"
        );

        foreach ($taskIds as $order => $id) {
            $stmt->execute([$order, $id, $status]);
        }

        return true;
    }

    /**
     * Reorder and optionally move a task to a different status
     * @param int $taskId The task ID to reorder
     * @param int $newOrder The new order position
     * @param string|null $newStatus Optional new status (null to keep current)
     */
    public function reorderTask(int $taskId, int $newOrder, ?string $newStatus = null): bool
    {
        $task = $this->getById($taskId);
        if (!$task) {
            return false;
        }

        $currentStatus = $task['status'];
        $status = $newStatus ?? $currentStatus;

        // If moving to a different status, adjust orders in both lists
        if ($newStatus !== null && $newStatus !== $currentStatus) {
            // Decrease order of tasks after the moved task in the old list
            $this->pdo->prepare(
                "UPDATE tasks SET task_order = task_order - 1, updated_at = NOW() 
                 WHERE status = ? AND task_order > ?"
            )->execute([$currentStatus, $task['task_order']]);

            // Increase order of tasks at or after the new position in the new list
            $this->pdo->prepare(
                "UPDATE tasks SET task_order = task_order + 1, updated_at = NOW() 
                 WHERE status = ? AND task_order >= ?"
            )->execute([$newStatus, $newOrder]);
        }

        // Update the task
        $stmt = $this->pdo->prepare(
            "UPDATE tasks SET task_order = ?, status = ?, updated_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$newOrder, $status, $taskId]);
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
    
    /**
     * Get all valid statuses
     */
    public function getStatuses(): array
    {
        return ['pending', 'completed'];
    }
}
