<?php
/**
 * Task Controller - Business logic
 */

namespace App\Controllers;

use App\Models\Task;

class TaskController
{
    private Task $taskModel;
    private array $messages = [];

    public function __construct()
    {
        $this->taskModel = new Task();
    }

    /**
     * List all tasks
     */
    public function list(): array
    {
        $allTasks = $this->taskModel->getAll();
        $pending = array_filter($allTasks, fn($task) => $task['status'] === 'pending');
        $completed = array_filter($allTasks, fn($task) => $task['status'] === 'completed');
        
        return [
            'pending' => array_values($pending),
            'completed' => array_values($completed),
            'stats' => $this->taskModel->getStats(),
            'messages' => $this->messages,
        ];
    }

    /**
     * Create a new task
     */
    public function create(string $title, string $description = ''): array
    {
        try {
            $id = $this->taskModel->create($title, $description);
            if ($id) {
                $this->messages[] = ['type' => 'success', 'text' => 'Task created successfully!'];
            }
        } catch (\InvalidArgumentException $e) {
            $this->messages[] = ['type' => 'error', 'text' => $e->getMessage()];
        }

        return $this->list();
    }

    /**
     * Update a task
     */
    public function update(int $id, string $title, string $description = ''): array
    {
        try {
            if ($this->taskModel->update($id, $title, $description)) {
                $this->messages[] = ['type' => 'success', 'text' => 'Task updated!'];
            }
        } catch (\InvalidArgumentException $e) {
            $this->messages[] = ['type' => 'error', 'text' => $e->getMessage()];
        }

        return $this->list();
    }

    /**
     * Toggle task status
     */
    public function toggleStatus(int $id): array
    {
        if ($this->taskModel->toggleStatus($id)) {
            $this->messages[] = ['type' => 'success', 'text' => 'Status updated!'];
        } else {
            $this->messages[] = ['type' => 'error', 'text' => 'Task not found'];
        }

        return $this->list();
    }

    /**
     * Delete a task
     */
    public function delete(int $id): array
    {
        if ($this->taskModel->delete($id)) {
            $this->messages[] = ['type' => 'success', 'text' => 'Task deleted!'];
        } else {
            $this->messages[] = ['type' => 'error', 'text' => 'Error deleting task'];
        }

        return $this->list();
    }
}
