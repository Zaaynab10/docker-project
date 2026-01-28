<?php
/**
 * Main application entry point
 * Modern MVC architecture with PSR-4 autoloader
 */

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', getenv('APP_DEBUG') ? '1' : '0');

// Autoloader
require_once __DIR__ . '/autoloader.php';

// Initialization
use App\Controllers\TaskController;
use App\Config\Config;

// Set timezone from config
date_default_timezone_set(Config::get('app.timezone', 'UTC'));

// Get action
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    $controller = new TaskController();
    $data = [];

    // Simple router
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $data = $controller->create($title, $description);
            } else {
                $data = $controller->list();
            }
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $data = $controller->delete($id);
                } else {
                    $data = $controller->list();
                }
            } else {
                $data = $controller->list();
            }
            break;

        case 'toggle':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $data = $controller->toggleStatus($id);
                } else {
                    $data = $controller->list();
                }
            } else {
                $data = $controller->list();
            }
            break;

        case 'list':
        default:
            $data = $controller->list();
            break;
    }

    // Extract data for the view
    extract($data);

    // Load the view
    require_once __DIR__ . '/Views/Tasks.php';

} catch (\Throwable $e) {
    // Error handling
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
            }
            .error-container {
                background: white;
                border-radius: 12px;
                padding: 40px;
                box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
                max-width: 600px;
                text-align: center;
            }
            h1 {
                color: #f56565;
                margin-bottom: 20px;
            }
            p {
                color: #4a5568;
                line-height: 1.6;
                margin-bottom: 15px;
            }
            .error-code {
                background: #fed7d7;
                color: #742a2a;
                padding: 15px;
                border-radius: 8px;
                margin-top: 20px;
                font-family: monospace;
                font-size: 0.9em;
                text-align: left;
                overflow-x: auto;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>Error</h1>
            <p>An error occurred:</p>
            <?php if (getenv('APP_DEBUG')): ?>
                <div class="error-code">
                    <strong><?= get_class($e) ?>:</strong><br>
                    <?= htmlspecialchars($e->getMessage()) ?><br><br>
                    <small><?= htmlspecialchars($e->getFile()) ?> (line <?= $e->getLine() ?>)</small>
                </div>
            <?php else: ?>
                <p>An internal error occurred. Please try again later.</p>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}
