<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Task Manager</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="app-container">
        <?php
        // Get date format from config
        use App\Config\Config;
        $dateFormat = Config::get('date.format', 'M d, Y - H:i');
        ?>

        <!-- Header -->
        <div class="app-header">
            <div>
                <h1>Medical Task Manager</h1>
                <p class="subtitle">Manage consultations and tasks efficiently</p>
            </div>
            <div class="stats">
                <div class="stat-item">
                    <span class="stat-number"><?= $stats['completed'] ?? 0 ?></span>
                    <span class="stat-label">Completed</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= $stats['pending'] ?? 0 ?></span>
                    <span class="stat-label">Pending</span>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $message): ?>
                <div class="alert alert-<?= htmlspecialchars($message['type']) ?>">
                    <?= htmlspecialchars($message['text']) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Create Task Form -->
        <section class="create-task-section">
            <h2>Add New Task</h2>
            <form method="POST" action="?action=create" class="task-form">
                <div class="form-group">
                    <input 
                        type="text" 
                        name="title" 
                        placeholder="Task title..." 
                        class="form-input" 
                        required
                        autofocus
                    >
                </div>
                <div class="form-group">
                    <textarea 
                        name="description" 
                        placeholder="Description (optional)" 
                        class="form-textarea"
                        rows="2"
                    ></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-add">
                    + Add Task
                </button>
            </form>
        </section>

        <!-- Task Lists -->
        <div class="tasks-container">
            <!-- Pending Tasks -->
            <section class="task-section">
                <h3 class="section-title">Pending Tasks</h3>
                <div class="tasks-list">
                    <?php if (empty($pending)): ?>
                        <p class="empty-state">No pending tasks. Great job!</p>
                    <?php else: ?>
                        <?php foreach ($pending as $task): ?>
                            <div class="task-item task-pending" data-id="<?= $task['id'] ?>" draggable="true">
                                <span class="drag-handle" title="Glisser pour déplacer">⋮⋮</span>
                                <form method="POST" action="?action=toggle" class="task-form-inline">
                                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                    <button type="submit" class="task-checkbox" title="Mark as completed">
                                        
                                    </button>
                                </form>
                                <div class="task-content">
                                    <h4 class="task-title"><?= htmlspecialchars($task['title']) ?></h4>
                                    <?php if (!empty($task['description'])): ?>
                                        <p class="task-description"><?= htmlspecialchars($task['description']) ?></p>
                                    <?php endif; ?>
                                    <small class="task-date">
                                        Created: <?= date($dateFormat, strtotime($task['created_at'])) ?>
                                    </small>
                                </div>
                                <span class="task-badge badge-pending">Pending</span>
                                <form method="POST" action="?action=delete" class="task-delete" onsubmit="return confirm('Delete this task?');">
                                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                    <button type="submit" class="btn-delete" title="Delete">✕</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Completed Tasks -->
            <section class="task-section">
                <h3 class="section-title">Completed Tasks</h3>
                <div class="tasks-list">
                    <?php if (empty($completed)): ?>
                        <p class="empty-state">No completed tasks yet.</p>
                    <?php else: ?>
                        <?php foreach ($completed as $task): ?>
                            <div class="task-item task-completed" data-id="<?= $task['id'] ?>" draggable="true">
                                <span class="drag-handle" title="Glisser pour déplacer">⋮⋮</span>
                                <form method="POST" action="?action=toggle" class="task-form-inline">
                                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                    <button type="submit" class="task-checkbox" title="Mark as pending">
                                        
                                    </button>
                                </form>
                                <div class="task-content">
                                    <h4 class="task-title"><?= htmlspecialchars($task['title']) ?></h4>
                                    <?php if (!empty($task['description'])): ?>
                                        <p class="task-description"><?= htmlspecialchars($task['description']) ?></p>
                                    <?php endif; ?>
                                    <small class="task-date">
                                        Created: <?= date($dateFormat, strtotime($task['created_at'])) ?>
                                    </small>
                                </div>
                                <span class="task-badge badge-completed">Completed</span>
                                <form method="POST" action="?action=delete" class="task-delete" onsubmit="return confirm('Delete this task?');">
                                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                    <button type="submit" class="btn-delete" title="Delete">✕</button>
                                </form>
                            </div>
                            <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- Footer -->
        <footer class="app-footer">
            <p>Medical Task Manager - Manage your schedule efficiently</p>
        </footer>
    </div>

    <script src="public/js/app.js"></script>
</body>
</html>                                                                                                                                                                  