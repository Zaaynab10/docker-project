# Medical Task Manager

A Dockerized medical task management application built with PHP 8.1+ and MySQL 8.0, following modern MVC architecture principles. Designed for managing consultations and medical tasks efficiently.

## Project Overview

This application provides a complete medical task management solution with Create, Read, Update, and Delete (CRUD) capabilities. It features a responsive web interface, real-time statistics, and secure database operations tailored for healthcare workflows.

## Architecture

The application follows the Model-View-Controller (MVC) design pattern for clean code organization and maintainability.

```
src/
├── index.php              # Application entry point
├── autoloader.php         # PSR-4 compliant autoloader
├── Config/
│   ├── Config.php        # Configuration management
│   └── Database.php      # Database connection handler
├── Controllers/
│   └── TaskController.php # Request handling and business logic
├── Models/
│   └── Task.php          # Data model and business rules
├── Views/
│   └── Tasks.php         # Presentation layer
└── public/
    ├── css/style.css     # Stylesheets
    └── js/app.js         # Client-side interactions
```

## Key Features

- Task creation with title and description
- Task listing with status indicators
- Status toggling between pending and completed
- Task deletion
- Real-time statistics dashboard
- Fully responsive design for mobile, tablet, and desktop devices

## Technical Specifications

### Backend
- PHP 8.1+ with PDO for database operations
- MySQL 8.0 for persistent storage
- PSR-4 autoloading for class management
- Singleton pattern for database connections

### Frontend
- HTML5 for semantic markup
- CSS3 with responsive design principles
- Vanilla JavaScript for interactivity

### Security Measures
- Prepared SQL statements to prevent SQL injection
- HTML escaping to mitigate XSS vulnerabilities
- Input validation on all user submissions

## Installation

### Prerequisites
- Docker Engine 20.10+
- Docker Compose v2.0+

### Setup

1. Clone the repository
2. Configure environment variables (see Environment Configuration)
3. Launch containers:

```bash
docker-compose up -d
```

The application will be available at `http://localhost:8080`

### PhpMyAdmin

PhpMyAdmin is accessible at `http://localhost:8081`


## Environment Configuration

Copy `.env.example` to `.env` and configure the following parameters:

```env
# Database Configuration
MYSQL_HOST=mysql
MYSQL_PORT=3306
MYSQL_DATABASE=your_database_name
MYSQL_USER=your_database_user
MYSQL_PASSWORD=your_secure_password
MYSQL_ROOT_PASSWORD=your_root_password
MYSQL_CHARSET=utf8mb4

# Server Configuration
SERVER_HOST=localhost
SERVER_PORT=8080
PHP_PORT=8080

# Application Configuration
APP_ENV=development
APP_DEBUG=true
APP_TIMEZONE=UTC
```

Refer to `.env.example` for complete documentation of all available variables.

## Extending the Application

### Adding a New Feature

1. **Model Layer**: Implement business logic in `src/Models/Task.php`
2. **Controller Layer**: Add request handlers in `src/Controllers/TaskController.php`
3. **View Layer**: Create or update templates in `src/Views/Tasks.php`
4. **Routing**: Register new routes in `src/index.php`

### Modifying the UI

- Styles: `src/public/css/style.css`
- JavaScript: `src/public/js/app.js`
- Layout: `src/Views/Tasks.php`

