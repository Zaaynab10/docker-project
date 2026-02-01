<?php
/**
 * Task Controller - Business logic
 */

namespace App\Controllers;

use App\Models\Task;
use App\Utils\Validator;
use App\Utils\CSRF;

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
            // Security: Validate inputs using Validator utility
            $validated = Validator::validateTaskInput([
                'title' => $title,
                'description' => $description
            ]);

            if (!empty($validated['errors'])) {
                foreach ($validated['errors'] as $field => $errors) {
                    foreach ($errors as $error) {
                        $this->messages[] = ['type' => 'error', 'text' => $error];
                    }
                }
                return $this->list();
            }

            $id = $this->taskModel->create($validated['title'], $validated['description']);
            if ($id) {
                $this->messages[] = ['type' => 'success', 'text' => 'Task created successfully!'];
                // Security: Regenerate CSRF token after successful operation to prevent duplicate submissions
                CSRF::regenerate();
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
            // Security: Validate inputs using Validator utility
            $validated = Validator::validateTaskInput([
                'title' => $title,
                'description' => $description
            ]);

            if (!empty($validated['errors'])) {
                foreach ($validated['errors'] as $field => $errors) {
                    foreach ($errors as $error) {
                        $this->messages[] = ['type' => 'error', 'text' => $error];
                    }
                }
                return $this->list();
            }

            if ($this->taskModel->update($id, $validated['title'], $validated['description'])) {
                $this->messages[] = ['type' => 'success', 'text' => 'Task updated!'];
                CSRF::regenerate();
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
        // Security: Validate ID
        $id = Validator::validateId($id);
        if ($id === null) {
            $this->messages[] = ['type' => 'error', 'text' => 'Invalid task ID'];
            return $this->list();
        }

        if ($this->taskModel->toggleStatus($id)) {
            $this->messages[] = ['type' => 'success', 'text' => 'Status updated!'];
            CSRF::regenerate();
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
        // Security: Validate ID
        $id = Validator::validateId($id);
        if ($id === null) {
            $this->messages[] = ['type' => 'error', 'text' => 'Invalid task ID'];
            return $this->list();
        }

        if ($this->taskModel->delete($id)) {
            $this->messages[] = ['type' => 'success', 'text' => 'Task deleted!'];
            CSRF::regenerate();
        } else {
            $this->messages[] = ['type' => 'error', 'text' => 'Error deleting task'];
        }

        return $this->list();
    }

    /**
     * Reorder tasks within a status
     */
    public function reorder(array $taskIds, string $status): array
    {
        if (empty($taskIds)) {
            return $this->list();
        }

        // Validate all task IDs
        $validatedIds = array_map(function($id) {
            return Validator::validateId($id);
        }, $taskIds);
        $validatedIds = array_filter($validatedIds, fn($id) => $id !== null);

        if (count($validatedIds) !== count($taskIds)) {
            $this->messages[] = ['type' => 'error', 'text' => 'Invalid task IDs'];
            return $this->list();
        }

        // Validate status
        $status = Validator::validateStatus($status);
        if ($status === null) {
            $this->messages[] = ['type' => 'error', 'text' => 'Invalid status'];
            return $this->list();
        }

        if ($this->taskModel->reorder(array_values($validatedIds), $status)) {
            $this->messages[] = ['type' => 'success', 'text' => 'Order updated!'];
        }

        return $this->list();
    }

    /**
     * Move a task to a different position and/or status
     */
    public function moveTask(int $id, int $newOrder, ?string $newStatus = null): array
    {
        $id = Validator::validateId($id);
        if ($id === null) {
            $this->messages[] = ['type' => 'error', 'text' => 'Invalid task ID'];
            return $this->list();
        }

        if ($newStatus !== null) {
            $newStatus = Validator::validateStatus($newStatus);
            if ($newStatus === null) {
                $this->messages[] = ['type' => 'error', 'text' => 'Invalid status'];
                return $this->list();
            }
        }

        if ($this->taskModel->reorderTask($id, $newOrder, $newStatus)) {
            $this->messages[] = ['type' => 'success', 'text' => 'Task moved!'];
        } else {
            $this->messages[] = ['type' => 'error', 'text' => 'Error moving task'];
        }

        return $this->list();
    }
}

