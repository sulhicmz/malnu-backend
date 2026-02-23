# Makefile for Malnu Backend
# Unified developer commands for common tasks
# Run `make help` to see all available commands

.PHONY: help setup install env up down restart logs shell ps \
        test test-coverage lint lint-fix analyse check \
        db-migrate db-reset db-seed db-fresh \
        docker-build docker-logs docker-shell docker-mysql docker-redis \
        clean cache-clear config-clear optimize \
        start stop watch

# Default target
.DEFAULT_GOAL := help

# Colors for output
BLUE := \033[0;34m
GREEN := \033[0;32m
YELLOW := \033[1;33m
NC := \033[0m

# ==============================================================================
# HELP
# ==============================================================================

help: ## Show this help message
	@echo ""
	@echo "$(BLUE)Malnu Backend - Available Commands$(NC)"
	@echo "$(BLUE)==================================$(NC)"
	@echo ""
	@awk 'BEGIN {FS = ":.*##"; section=""} \
		/^## / { section=substr($$0, 4); printf "\n$(YELLOW)%s$(NC)\n", section } \
		/^[a-zA-Z0-9_-]+:.*##/ { printf "  $(GREEN)%-18s$(NC) %s\n", $$1, $$2 }' $(MAKEFILE_LIST)
	@echo ""

# ==============================================================================
## Setup & Installation
## ==============================================================================

setup: install env db-migrate ## Complete project setup (install deps, env, migrate)
	@echo "$(GREEN)✓ Setup complete!$(NC)"
	@echo ""
	@echo "$(YELLOW)Next steps:$(NC)"
	@echo "  1. Run 'make up' to start Docker services"
	@echo "  2. Run 'make db-seed' to populate test data"
	@echo "  3. Run 'make start' to start the application"
	@echo ""

install: ## Install PHP dependencies
	composer install

install-prod: ## Install PHP dependencies for production (no dev)
	composer install --no-dev --optimize-autoloader

env: ## Setup environment file from .env.example
	@if [ ! -f .env ]; then \
		cp .env.example .env && \
		echo "$(GREEN)✓ Created .env from .env.example$(NC)"; \
		echo "$(YELLOW)  Remember to update APP_KEY and JWT_SECRET$(NC)"; \
	else \
		echo "$(YELLOW).env already exists, skipping...$(NC)"; \
	fi

env-setup: ## Run the interactive environment setup script
	./scripts/setup-env.sh

# ==============================================================================
## Docker
## ==============================================================================

up: ## Start all Docker services
	docker-compose up -d

down: ## Stop all Docker services
	docker-compose down

restart: down up ## Restart all Docker services

ps: ## Show Docker service status
	docker-compose ps

logs: ## Show Docker logs (all services)
	docker-compose logs -f --tail=100

logs-app: ## Show app container logs
	docker-compose logs -f --tail=100 app

logs-frontend: ## Show frontend container logs
	docker-compose logs -f --tail=100 frontend

docker-build: ## Rebuild Docker containers
	docker-compose build --no-cache

docker-shell: ## Open shell in app container
	docker-compose exec app sh

docker-mysql: ## Connect to MySQL in container
	docker-compose exec mysql mysql -u malnu_user -pmalnu_password_change_in_production malnu

docker-redis: ## Connect to Redis in container
	docker-compose exec redis redis-cli

tools: ## Start admin tools (Adminer, Redis Commander)
	docker-compose --profile tools up -d

# ==============================================================================
## Code Quality
## ==============================================================================

test: ## Run PHPUnit tests
	composer test

test-coverage: ## Run tests with HTML coverage report
	php vendor/bin/co-phpunit --prepend tests/bootstrap.php -c phpunit.xml.dist --coverage-html coverage

lint: ## Check code style (dry-run, no changes)
	vendor/bin/php-cs-fixer fix --dry-run --diff

lint-fix: ## Auto-fix code style issues
	composer cs-fix

analyse: ## Run PHPStan static analysis
	composer analyse

check: lint analyse test ## Run all checks (lint, analyse, test)

# ==============================================================================
## Database
## ==============================================================================

db-migrate: ## Run database migrations
	php artisan migrate

db-migrate-fresh: ## Reset database and run all migrations
	php artisan migrate:fresh

db-reset: db-migrate-fresh db-seed ## Reset database with seed data

db-seed: ## Run database seeders
	php artisan db:seed

db-fresh: ## Fresh migration with seed (alias for db-reset)
	$(MAKE) db-reset

# ==============================================================================
## Application
## ==============================================================================

start: ## Start the application server
	composer start

stop: ## Stop the application server
	php artisan stop

watch: ## Start with hot-reload (file watcher)
	php artisan watch

# ==============================================================================
## Cache & Optimization
## ==============================================================================

cache-clear: ## Clear all cache
	php artisan cache:clear
	php artisan config:clear
	php artisan route:clear

config-clear: ## Clear configuration cache
	php artisan config:clear

optimize: ## Optimize for production
	composer dump-autoload --optimize
	php artisan config:cache
	php artisan route:cache

# ==============================================================================
## Cleanup
## ==============================================================================

clean: ## Clean generated files and cache
	rm -rf runtime/container
	rm -rf runtime/cache/*
	rm -rf coverage
	php artisan cache:clear
	php artisan config:clear
