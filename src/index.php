<?php
/**
 * Main application entry point
 * Modern MVC architecture with PSR-4 autoloader
 */

// Security: Start session early
if (session_status() === PHP_SESSION_NONE) {
    $secure = getenv('APP_ENV') === 'production';
    $httponly = true;
    session_start([
        'cookie_secure' => $secure,
        'cookie_httponly' => $httponly,
        'cookie_samesite' => 'Strict'
    ]);
}

// Security: Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: ' . (getenv('APP_ENV') === 'production' ? 'DENY' : 'SAMEORIGIN'));
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Security: Content Security Policy
$cspHeader = "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'";
header("Content-Security-Policy: $cspHeader");

// Error handling
$appDebug = filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN);
error_reporting(E_ALL);
ini_set('display_errors', $appDebug ? '1' : '0');

// Autoloader
require_once __DIR__ . '/autoloader.php';

// Initialization
use App\Controllers\TaskController;
use App\Config\Config;
use App\Utils\CSRF;
use App\Utils\Validator;

// Set timezone from config
date_default_timezone_set(Config::get('app.timezone', 'UTC'));

// Generate CSRF token ONCE and reuse it consistently
$csrfToken = CSRF::getToken() ?? CSRF::generate();
$csrfInput = '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '">';

// Get action - validate against whitelist
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$action = Validator::validateAction($action);
if ($action === null) {
    $action = 'list';
}

// Store CSRF token in session for validation
$_SESSION['csrf_token'] = $csrfToken;

try {
    $controller = new TaskController();
    $data = [];
    $validationErrors = [];

    // Simple router
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Security: Validate CSRF token first
                if (!CSRF::isValidPost()) {
                    $validationErrors[] = ['type' => 'error', 'text' => 'Invalid form submission. Please try again.'];
                    $data = $controller->list();
                    $data['messages'] = array_merge($validationErrors, $data['messages'] ?? []);
                    break;
                }

                // Security: Validate and sanitize inputs
                $inputData = [
                    'title' => $_POST['title'] ?? '',
                    'description' => $_POST['description'] ?? ''
                ];

                $validated = Validator::validateTaskInput($inputData);

                if (!empty($validated['errors'])) {
                    // Format validation errors
                    foreach ($validated['errors'] as $field => $errors) {
                        foreach ($errors as $error) {
                            $validationErrors[] = ['type' => 'error', 'text' => ucfirst($field) . ': ' . $error];
                        }
                    }
                    $data = $controller->list();
                    $data['messages'] = array_merge($validationErrors, $data['messages'] ?? []);
                } else {
                    $data = $controller->create($validated['title'], $validated['description']);
                }
            } else {
                $data = $controller->list();
            }
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Security: Validate CSRF token first
                if (!CSRF::isValidPost()) {
                    $validationErrors[] = ['type' => 'error', 'text' => 'Invalid form submission. Please try again.'];
                    $data = $controller->list();
                    $data['messages'] = array_merge($validationErrors, $data['messages'] ?? []);
                    break;
                }

                $id = Validator::validateId($_POST['id'] ?? 0);
                if ($id !== null && $id > 0) {
                    $data = $controller->delete($id);
                } else {
                    $validationErrors[] = ['type' => 'error', 'text' => 'Invalid task ID'];
                    $data = $controller->list();
                    $data['messages'] = array_merge($validationErrors, $data['messages'] ?? []);
                }
            } else {
                $data = $controller->list();
            }
            break;

        case 'toggle':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Security: Validate CSRF token first
                if (!CSRF::isValidPost()) {
                    $validationErrors[] = ['type' => 'error', 'text' => 'Invalid form submission. Please try again.'];
                    $data = $controller->list();
                    $data['messages'] = array_merge($validationErrors, $data['messages'] ?? []);
                    break;
                }

                $id = Validator::validateId($_POST['id'] ?? 0);
                if ($id !== null && $id > 0) {
                    $data = $controller->toggleStatus($id);
                } else {
                    $validationErrors[] = ['type' => 'error', 'text' => 'Invalid task ID'];
                    $data = $controller->list();
                    $data['messages'] = array_merge($validationErrors, $data['messages'] ?? []);
                }
            } else {
                $data = $controller->list();
            }
            break;

        case 'reorder':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Security: Validate CSRF token first
                if (!CSRF::isValidPost()) {
                    $validationErrors[] = ['type' => 'error', 'text' => 'Invalid form submission. Please try again.'];
                    $data = $controller->list();
                    $data['messages'] = array_merge($validationErrors, $data['messages'] ?? []);
                    break;
                }

                $taskIds = $_POST['task_ids'] ?? [];
                $status = $_POST['status'] ?? 'pending';

                if (is_string($taskIds)) {
                    $taskIds = json_decode($taskIds, true) ?? [];
                }

                $data = $controller->reorder($taskIds, $status);
            } else {
                $data = $controller->list();
            }
            break;

        case 'move':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Security: Validate CSRF token first
                if (!CSRF::isValidPost()) {
                    $validationErrors[] = ['type' => 'error', 'text' => 'Invalid form submission. Please try again.'];
                    $data = $controller->list();
                    $data['messages'] = array_merge($validationErrors, $data['messages'] ?? []);
                    break;
                }

                $id = Validator::validateId($_POST['id'] ?? 0);
                $newOrder = (int)($_POST['order'] ?? 0);
                $newStatus = !empty($_POST['status']) ? Validator::validateStatus($_POST['status']) : null;

                if ($id !== null && $id > 0 && $newOrder >= 0) {
                    $data = $controller->moveTask($id, $newOrder, $newStatus);
                } else {
                    $validationErrors[] = ['type' => 'error', 'text' => 'Invalid parameters'];
                    $data = $controller->list();
                    $data['messages'] = array_merge($validationErrors, $data['messages'] ?? []);
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
    // Security: Generic error message in production
    http_response_code(500);
    
    // Log the actual error (in production, log to file instead of display)
    error_log('Application Error: ' . get_class($e) . ' - ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    
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
            <?php if ($appDebug): ?>
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

