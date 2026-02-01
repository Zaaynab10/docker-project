# Medical Task Manager

<div align="center">

[![Docker](https://img.shields.io/badge/Docker-20.10+-2496ED?style=flat-square&logo=docker)](https://www.docker.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)](https://www.mysql.com/)
[![Apache](https://img.shields.io/badge/Apache-2.4-D22128?style=flat-square&logo=apache)](https://httpd.apache.org/)
[![License](https://img.shields.io/badge/License-MIT-yellow?style=flat-square)](LICENSE)

A production-ready Dockerized medical task management application built with PHP 8.2+ and MySQL 8.0, following modern MVC architecture principles with enterprise-grade security.

</div>

---

## Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Architecture](#architecture)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Environment Configuration](#environment-configuration)
- [Usage](#usage)
  - [Development Commands](#development-commands)
  - [Production Deployment](#production-deployment)
  - [Database Management](#database-management)
- [Security](#security)
- [Project Structure](#project-structure)
- [API Reference](#api-reference)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

---

## Overview

Medical Task Manager is a comprehensive task management solution designed specifically for healthcare workflows. Built with modern web technologies and containerized for easy deployment, it provides a secure, scalable platform for managing medical consultations, patient tasks, and administrative workflows.

### Key Highlights

- **Production-Ready**: Hardened Docker containers with 55 security vulnerabilities fixed
- **MVC Architecture**: Clean, maintainable code structure
- **Enterprise Security**: Read-only filesystems, non-root users, comprehensive headers
- **DevOps Ready**: Full Makefile automation, production and development environments
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices

---

## Key Features

| Feature | Description |
|---------|-------------|
| Task Management | Create, read, update, and delete tasks with rich metadata |
| Real-time Dashboard | Live statistics and task status overview |
| Responsive UI | Mobile-first design for all device sizes |
| Status Workflows | Toggle tasks between pending and completed states |
| Database Management | Backup, restore, and migration support |
| Input Validation | Server-side validation with CSRF protection |
| Audit Ready | Comprehensive logging and error tracking |

---

## Architecture

```
+---------------------------------------------------------------------+
|                     Load Balancer / Reverse Proxy                   |
+-----------------------------+---------------------------------------+
                              |
                              v
+---------------------------------------------------------------------+
|                     Docker Network                                  |
|  +-------------+  +-------------+  +-----------------------------+  |
|  |   Apache    |  |   MySQL     |  |      phpMyAdmin             |  |
|  |   PHP 8.2   |  |   8.0       |  |    (Development)            |  |
|  |  Container  |  |  Container  |  |     Container               |  |
|  +-------------+  +-------------+  +-----------------------------+  |
|         |               |                     |                    |
|         +---------------+---------------------+                    |
|                              |                                     |
|              +---------------+--------------+                      |
|              |   Read-Only Volume           |                      |
|              |    (Application)             |                      |
|              +------------------------------+                      |
+---------------------------------------------------------------------+
```

### Technology Stack

| Layer | Technology | Version |
|-------|------------|---------|
| Backend | PHP | 8.2+ |
| Database | MySQL | 8.0.45 |
| Web Server | Apache | 2.4 |
| Frontend | HTML5, CSS3, Vanilla JS | - |
| Containerization | Docker | 20.10+ |
| Orchestration | Docker Compose | 2.0+ |

---

## Getting Started

### Prerequisites

| Requirement | Minimum Version | Recommended |
|-------------|-----------------|-------------|
| Docker Engine | 20.10+ | Latest stable |
| Docker Compose | v2.0+ | v2.20+ |
| Make | 3.0+ | Latest |
| Git | 2.0+ | Latest |

### Installation

1. **Clone the repository**

```bash
git clone <repository-url>
cd todo_app
```

2. **Configure environment**

```bash
# Copy environment template
cp .env.example .env

# Edit configuration (see Environment Configuration below)
nano .env
```

3. **Launch the application**

```bash
# Start development environment
make up

# Verify containers are running
make status
```

4. **Access the application**

| Service | URL | Credentials |
|---------|-----|-------------|
| Application | http://localhost:8080 | - |
| phpMyAdmin | http://localhost:8081 | From `.env` |

### Environment Configuration

Copy `.env.example` to `.env` and configure the following parameters:

```env
# =============================================================================
# Database Configuration
# =============================================================================
MYSQL_HOST=mysql
MYSQL_PORT=3306
MYSQL_DATABASE=todo_db
MYSQL_USER=appuser
MYSQL_PASSWORD=apppassword
MYSQL_ROOT_PASSWORD=rootpassword
MYSQL_CHARSET=utf8mb4

# =============================================================================
# Server Configuration
# =============================================================================
SERVER_HOST=localhost
SERVER_PORT=8080

# =============================================================================
# Application Configuration
# =============================================================================
APP_ENV=development    # development | production
APP_DEBUG=true        # true | false
APP_TIMEZONE=UTC

# =============================================================================
# phpMyAdmin Configuration
# =============================================================================
PHPMYADMIN_PORT=8081
```

---

## Usage

### Development Commands

| Command | Description |
|---------|-------------|
| `make up` | Start development environment |
| `make down` | Stop development environment |
| `make restart` | Restart development environment |
| `make logs` | View all logs (follow mode) |
| `make logs-app` | View application logs only |
| `make logs-db` | View database logs only |

### Build Commands

| Command | Description |
|---------|-------------|
| `make build` | Build Docker images (no cache) |
| `make rebuild` | Rebuild application container only |
| `make clean` | Clean containers and networks |
| `make prune` | Prune all Docker resources |

### Production Deployment

| Command | Description |
|---------|-------------|
| `make prod-up` | Start production environment |
| `make prod-down` | Stop production environment |
| `make prod-logs` | View production logs |
| `make prod-build` | Build production images |

### Database Management

| Command | Description |
|---------|-------------|
| `make db-init` | Initialize database schema |
| `make db-backup` | Create database backup |
| `make db-restore` | Restore from backup file |
| `make db-migrate` | Run database migrations |

### Maintenance Commands

| Command | Description |
|---------|-------------|
| `make status` | Show container status |
| `make shell` | Open shell in app container |
| `make db-shell` | Open MySQL CLI |
| `make phpmyadmin` | Open phpMyAdmin in browser |
| `make info` | Show environment information |
| `make help` | Show all available commands |
| `make reset` | Reset environment (stop + remove volumes + start) |

---

## Security

### Security Features

| Feature | Implementation |
|---------|----------------|
| Container Hardening | Read-only filesystem for all containers |
| Privilege Management | Non-root users (www-data, mysql) |
| Security Headers | X-Content-Type-Options, X-Frame-Options, X-XSS-Protection |
| Cookie Security | HttpOnly, Secure, SameSite=Strict |
| Server Hardening | ServerTokens Prod, ServerSignature Off, TraceEnable Off |
| SQL Injection Protection | Prepared statements with PDO |
| XSS Protection | HTML escaping on all output |
| Input Validation | Server-side validation with CSRF protection |


---

## Project Structure

```
todo_app/
├── src/
│   ├── index.php              # Application entry point
│   ├── autoloader.php         # PSR-4 compliant autoloader
│   ├── Config/
│   │   ├── Config.php         # Configuration management
│   │   └── Database.php       # Database connection handler
│   ├── Controllers/
│   │   └── TaskController.php # Request handling and business logic
│   ├── Models/
│   │   └── Task.php           # Data model and business rules
│   ├── Views/
│   │   └── Tasks.php          # Presentation layer
│   ├── Utils/
│   │   ├── CSRF.php           # CSRF token generation/validation
│   │   └── Validator.php      # Input validation utilities
│   └── public/
│       ├── css/style.css      # Stylesheets
│       └── js/app.js          # Client-side interactions
├── docker-compose.yml         # Development environment
├── docker-compose.prod.yml    # Production environment
├── Dockerfile                 # Application container image
├── Makefile                   # Development automation
├── mysql/
│   └── init.sql               # Database initialization
├── apache/
│   └── vhost.conf             # Apache virtual host configuration
├── .env.example               # Environment template
└── reset.sh                   # Reset development environment
```

---

## API Reference

### Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | Main dashboard |
| GET | `/tasks` | List all tasks |
| POST | `/tasks/create` | Create new task |
| POST | `/tasks/toggle/{id}` | Toggle task status |
| POST | `/tasks/delete/{id}` | Delete task |
| POST | `/tasks/reorder` | Reorder tasks within a status |

### Request Examples

**Create Task**
```bash
curl -X POST http://localhost:8080/tasks/create \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "title=Patient Consultation&description=Annual checkup"
```

**Toggle Task Status**
```bash
curl -X POST http://localhost:8080/tasks/toggle/1
```

---

## Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| Container fails to start | Check `.env` configuration and run `make logs` |
| Database connection refused | Ensure MySQL is healthy: `make status` |
| Port already in use | Change `PHP_PORT` in `.env` file |
| Permission denied errors | Ensure user is in docker group: `sudo usermod -aG docker $USER` |
| Application not accessible | Check Apache logs: `make logs-app` |

### Diagnostic Commands

```bash
# Check container status
make status

# View application logs
make logs-app

# View database logs
make logs-db

# Show environment info
make info
```

---

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Coding Standards

- Follow PSR-12 coding style
- Use meaningful variable and function names
- Add comments for complex logic
- Write descriptive commit messages

---

---

## Support

For support and questions:

1. Check the [Troubleshooting](#troubleshooting) section
2. Review application logs: `make logs`
3. Open an issue on GitHub

---

<div align="center">

**Built for Healthcare Professionals**

</div>

