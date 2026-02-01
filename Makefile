.PHONY: help up down stop restart logs build rebuild clean prune \
        prod-up prod-down prod-logs db-init db-migrate db-backup \
        db-restore status shell db-shell phpmyadmin info

# Variables
COMPOSE_FILE = docker-compose.yml
COMPOSE_FILE_PROD = docker-compose.prod.yml
APP_NAME = todo_app
DB_NAME = todo_db

# DEVELOPMENT COMMANDS

# Start development environment
up:
	@echo "Starting development environment..."
	docker compose -f $(COMPOSE_FILE) up -d

# Stop development environment
down:
	@echo "Stopping development environment..."
	docker compose -f $(COMPOSE_FILE) down

# Restart development environment
restart: down up

# View logs (all services)
logs:
	docker compose -f $(COMPOSE_FILE) logs -f

# View logs for specific service
logs-app:
	docker compose -f $(COMPOSE_FILE) logs -f app

logs-db:
	docker compose -f $(COMPOSE_FILE) logs -f mysql

# BUILD COMMANDS

# Build images without cache
build:
	@echo "Building Docker images..."
	docker compose -f $(COMPOSE_FILE) build --no-cache

# Rebuild application container only
rebuild:
	@echo "Rebuilding application container..."
	docker compose -f $(COMPOSE_FILE) build app --no-cache

# Clean containers and networks
clean:
	@echo "Cleaning up containers and networks..."
	docker compose -f $(COMPOSE_FILE) down --remove-orphans

# Prune all Docker resources
prune:
	@echo "Pruning Docker system..."
	@read -p "This will remove all stopped containers, networks, and volumes. Continue? [y/N] " -n 1 -r; \
	if $$REPLY =~ ^[Yy]$$; then \
		echo ""; \
		docker system prune -af --volumes; \
	fi
# Reset development environment: stop + remove volumes + start fresh
reset:
	@echo "Resetting development environment..."
	docker compose -f $(COMPOSE_FILE) down -v
	docker compose -f $(COMPOSE_FILE) up -d
	@echo "Environment reset complete!"

# PRODUCTION COMMANDS

# Start production environment
prod-up:
	@echo "Starting production environment..."
	docker compose -f $(COMPOSE_FILE_PROD) up -d

# Stop production environment
prod-down:
	@echo "Stopping production environment..."
	docker compose -f $(COMPOSE_FILE_PROD) down

# Production logs
prod-logs:
	docker compose -f $(COMPOSE_FILE_PROD) logs -f

# Build production images
prod-build:
	@echo "Building production images..."
	docker compose -f $(COMPOSE_FILE_PROD) build --no-cache

# DATABASE COMMANDS

# Initialize database
db-init:
	@echo "Initializing database..."
	docker compose -f $(COMPOSE_FILE) exec mysql mysql -u root -p${MYSQL_ROOT_PASSWORD} -e "CREATE DATABASE IF NOT EXISTS ${MYSQL_DATABASE};"

# Migrate database (placeholder for future migrations)
db-migrate:
	@echo "Running database migrations..."
	@echo "No migrations available yet."

# Backup database
db-backup:
	@echo "Creating database backup..."
	@BACKUP_FILE="backup_$$(date +%Y%m%d_%H%M%S).sql"; \
	docker compose -f $(COMPOSE_FILE) exec mysql mysqldump -u ${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE} > $$BACKUP_FILE; \
	echo "Backup saved to: $$BACKUP_FILE"

# Restore database from backup
db-restore:
	@echo "Restoring database..."
	@read -p "Enter backup file path: " BACKUP_FILE; \
	if [ -f "$$BACKUP_FILE" ]; then \
		docker compose -f $(COMPOSE_FILE) exec -T mysql mysql -u ${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE} < $$BACKUP_FILE; \
		echo "Database restored successfully."; \
	else \
		echo "Backup file not found: $$BACKUP_FILE"; \
	fi

# MAINTENANCE COMMANDS

# Show container status
status:
	@echo "Container Status:"
	@docker compose -f $(COMPOSE_FILE) ps

# Open shell in application container
shell:
	@echo "Opening shell in application container..."
	docker compose -f $(COMPOSE_FILE) exec app bash

# Open MySQL CLI
db-shell:
	@echo "Opening MySQL CLI..."
	docker compose -f $(COMPOSE_FILE) exec mysql mysql -u ${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE}

# Open phpMyAdmin in browser
phpmyadmin:
	@echo "Opening phpMyAdmin..."
	@if command -v xdg-open >/dev/null 2>&1; then \
		xdg-open http://localhost:8081; \
	elif command -v open >/dev/null 2>&1; then \
		open http://localhost:8081; \
	else \
		echo "phpMyAdmin available at: http://localhost:8081"; \
	fi

# Show application info
info:
	@echo "Medical Task Manager - Environment Info"
	@echo "App URL:      http://localhost:8080"
	@echo "phpMyAdmin:   http://localhost:8081"
	@echo "Database:     MySQL 8.0"
	@echo "PHP Version:  8.2"
	@echo ""
	@echo "Security Features:"
	@echo "  - Read-only filesystem for all containers"
	@echo "  - Non-root user (www-data, mysql)"
	@echo "  - No new privileges security option"
	@echo "  - phpMyAdmin with security hardening"

# Show help
help:
	@echo "Medical Task Manager - Available Commands"
	@echo ""
	@echo "DEVELOPMENT:"
	@echo "  make up         Start development environment"
	@echo "  make down       Stop development environment"
	@echo "  make restart    Restart development environment"
	@echo "  make logs       View all logs"
	@echo "  make logs-app   View app logs"
	@echo "  make logs-db    View database logs"
	@echo ""
	@echo "BUILD:"
	@echo "  make build      Build Docker images"
	@echo "  make rebuild    Rebuild app container"
	@echo "  make clean      Clean containers/networks"
	@echo "  make prune      Prune all Docker resources"
	@echo ""
	@echo "PRODUCTION:"
	@echo "  make prod-up    Start production environment"
	@echo "  make prod-down  Stop production environment"
	@echo "  make prod-logs  View production logs"
	@echo ""
	@echo "DATABASE:"
	@echo "  make db-init    Initialize database"
	@echo "  make db-backup  Create database backup"
	@echo "  make db-restore Restore database from backup"
	@echo ""
	@echo "MAINTENANCE:"
	@echo "  make status     Show container status"
	@echo "  make shell      Open shell in app container"
	@echo "  make db-shell   Open MySQL CLI"
	@echo "  make phpmyadmin Open phpMyAdmin in browser"
	@echo "  make info       Show environment info"
	@echo "  make help       Show this help message"
